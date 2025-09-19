<?php

namespace App\Modules\Statistics\Application\UseCases\About;

use App\Modules\Base\Application\Response\DataStatus;
use App\Modules\Base\Application\Response\DataSuccess;
use App\Modules\Base\Application\Response\DataFailed;
use App\Modules\Base\Domain\Enums\AuthGurdTypeEnum;
use App\Modules\Base\Domain\Holders\AuthHolder;
use App\Modules\Blog\Application\Enums\View\ViewTypeEnum;
use App\Modules\Statistics\Application\DTOS\About\AboutDTO;
use App\Modules\Statistics\Application\DTOS\About\AboutEmailsDto;
use App\Modules\Statistics\Application\DTOS\About\AboutFilterDTO;
use App\Modules\Statistics\Application\DTOS\About\AboutPhonesDto;
use App\Modules\Statistics\Application\DTOS\About\AboutSocialsDto;
use App\Modules\Statistics\Application\DTOS\About\AboutWorkingHoursDto;
use App\Modules\Statistics\Http\Resources\Api\MobileAboutDetailResource;
use App\Modules\Statistics\Http\Resources\Api\About\AboutResource as MobileAboutResource;
use App\Modules\Statistics\Http\Resources\Api\AboutSocial\AboutSocialResource;
use App\Modules\Statistics\Http\Resources\Dashboard\About\AboutDetailResource as DashboardAboutDetailResource;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutEmail\AboutEmailRepository;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutPhone\AboutPhoneRepository;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\About\AboutRepository;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutSocial\AboutSocialsRepository;
use App\Modules\Statistics\Infrastructure\Persistence\Repositories\AboutWorkingHours\AboutWorkingHoursRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use Socket;

class AboutUseCase
{
    protected $aboutRepository;
    protected $aboutPhoneRepository;
    protected $aboutEmailRepository;
    protected $aboutSocialsRepository;
    protected $employee;
    protected $aboutWorkingHoursRepository;

    public function __construct(
        AboutRepository $aboutRepository,
        AboutPhoneRepository $aboutPhoneRepository,
        AboutEmailRepository $aboutEmailsRepository,
        AboutSocialsRepository $aboutSocialsRepository,
        AboutWorkingHoursRepository $aboutWorkingHoursRepository
    ) {
        $this->aboutRepository = $aboutRepository;
        $this->aboutPhoneRepository = $aboutPhoneRepository;
        $this->aboutEmailRepository = $aboutEmailsRepository;
        $this->aboutSocialsRepository = $aboutSocialsRepository;
        $this->aboutWorkingHoursRepository = $aboutWorkingHoursRepository;
        $this->employee = AuthHolder::getInstance()->getAuth(AuthGurdTypeEnum::EMPLOYEE->value);
    }

    public function fetchAbouts(AboutFilterDTO $aboutFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $about = $this->aboutRepository->getFirst();
            $about = $about ? $about : null;
            $resource = $about ? $this->HandelAboutDetailResource(
                $about,
                $view,
            ) : (object) [];
            return DataSuccess(
                status: true,
                message: 'about fetched successfully',
                data: $aboutFilterDto->paginate ? $resource->response()->getData() : $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function fetchContactInfos(AboutFilterDTO $aboutFilterDto, $view = ViewTypeEnum::DASHBOARD->value): DataStatus
    {
        try {
            $about = $this->aboutRepository->getFirst();
            $about = $about ? $about : null;
            $whatsapp = $about ? $about->socials()->where('key', 'whatsapp')->first() : null;
            $resource = $whatsapp ? AboutSocialResource::collection([$whatsapp]) : [];
            return DataSuccess(
                status: true,
                message: 'contact info fetched successfully',
                data: $resource
            );
        } catch (\Exception $e) {
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    public function createOrUpdateAbout(AboutDTO $aboutDTO): DataStatus
    {
        try {
            DB::beginTransaction();
            /* if (isset($aboutDTO->about_id)) {
                $aboutDTO->updated_by = $this->employee->id;
            } else {
                $aboutDTO->created_by = $this->employee->id;
            } */

            // Step 1: Create or update about
            $about = $this->aboutRepository->updateOrCreate($aboutDTO);
            $about->refresh();

            // Debug logs: capture incoming working hours and created about id
            Log::info('AboutUseCase:createOrUpdateAbout - aboutDTO.working_hours', ['working_hours' => $aboutDTO->working_hours ?? null]);
            Log::info('AboutUseCase:createOrUpdateAbout - about id', ['about_id' => $about->id ?? null]);

            // Step 2: Create or update about details
            $aboutDTO->about_id = $about->id;
            $aboutDetails = $this->handleAboutDetailsData($aboutDTO);
            DB::commit();

            // Reload about and its relations so the returned resource contains fresh working hours, phones, emails, socials
            $about->refresh();
            $about->load(['workingHours', 'phones', 'emails', 'socials']);

            return DataSuccess(
                status: true,
                message: isset($aboutDTO->about_id)
                ? 'About updated successfully'
                : 'About created successfully',
                data: new MobileAboutResource($about)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return DataFailed(
                status: false,
                message: $e->getMessage()
            );
        }
    }

    private function handleAboutDetailsData(AboutDTO $aboutDTO): DataStatus
    {
        try {
            // first phones
            if (isset($aboutDTO->phones) && count($aboutDTO->phones) > 0) {
                $this->aboutPhoneRepository->deleteByAboutId($aboutDTO->about_id);
                foreach ($aboutDTO->phones as $phone) {
                    $aboutPhoneDto = new AboutPhonesDto([
                        'phone' => $phone,
                        'about_id' => $aboutDTO->about_id
                    ]);
                    $this->aboutPhoneRepository->create($aboutPhoneDto);
                }
            }
            //second emails
            if (isset($aboutDTO->emails) && count($aboutDTO->emails) > 0) {
                $this->aboutEmailRepository->deleteByAboutId($aboutDTO->about_id);
                foreach ($aboutDTO->emails as $email) {
                    $aboutEmailDto = new AboutEmailsDto([
                        'email' => $email,
                        'about_id' => $aboutDTO->about_id
                    ]);
                    $this->aboutEmailRepository->create($aboutEmailDto);
                }
            }
            //third socials
            if (isset($aboutDTO->socials) && count($aboutDTO->socials) > 0) {
                // dd($aboutDTO->socials);
                $sentSocialIds = array_filter(
                    array_column($aboutDTO->socials, 'id'),
                    fn($id) => !is_null($id)
                );
                // dd($sentSocialIds);
                // 2. Delete socials for this about_id that are NOT in the sent IDs
                // $this->aboutSocialsRepository->deleteByAboutIdExceptIds($aboutDTO->about_id, $sentSocialIds);
                foreach ($aboutDTO->socials as $key => $social) {
                    // dd($social['key']);
                    $aboutSocialDto = AboutSocialsDto::fromArray([
                        // 'id' => $social[$key]['id'],
                        'key' => $social['key'],
                        'value' => $social['value'],
                        'about_id' => $aboutDTO->about_id,
                        'image' => $social['image']
                    ]);
                    // dd($aboutSocialDto);
                    $this->aboutSocialsRepository->updateOrCreate($aboutSocialDto);
                }
            }

            // Working Hours
            Log::info('AboutUseCase:createOrUpdateAbout - working_hours check', ['isset' => isset($aboutDTO->working_hours), 'type' => gettype($aboutDTO->working_hours), 'count' => is_array($aboutDTO->working_hours) ? count($aboutDTO->working_hours) : null, 'raw' => $aboutDTO->working_hours ?? null]);
            if (isset($aboutDTO->working_hours) && count($aboutDTO->working_hours) > 0) {
                $this->aboutWorkingHoursRepository->deleteByAboutId($aboutDTO->about_id);
                foreach ($aboutDTO->working_hours as $index => $workingHour) {
                    // Normalize times: if from_time/to_time are nested under 'days', use them
                    $days = $workingHour['days'] ?? [];
                    $fromTime = $workingHour['from_time'] ?? ($days['from_time'] ?? null);
                    $toTime = $workingHour['to_time'] ?? ($days['to_time'] ?? null);

                    // Format times to H:i:s when possible (accept H:i or H:i:s)
                    $normalizeTime = function ($t) {
                        if (!$t)
                            return null;
                        // if 'HH:MM' add seconds
                        if (preg_match('/^\d{2}:\d{2}$/', $t)) {
                            return $t . ':00';
                        }
                        // if already HH:MM:SS, return as-is
                        if (preg_match('/^\d{2}:\d{2}:\d{2}$/', $t)) {
                            return $t;
                        }
                        // fallback: attempt strtotime
                        $ts = strtotime($t);
                        return $ts ? date('H:i:s', $ts) : null;
                    };

                    $fromTime = $normalizeTime($fromTime);
                    $toTime = $normalizeTime($toTime);

                    // Remove any from_time/to_time nested inside days so days will only carry the 'title' array
                    if (is_array($days)) {
                        unset($days['from_time'], $days['to_time']);
                    }

                    $aboutWorkingHoursDto = new AboutWorkingHoursDto([
                        'from_time' => $fromTime,
                        'to_time' => $toTime,
                        'days' => $days,
                        'about_id' => $aboutDTO->about_id
                    ]);
                    try {
                        Log::info('AboutUseCase:createOrUpdateAbout - creating working_hour', ['index' => $index, 'dto' => $aboutWorkingHoursDto->toArray()]);
                        $this->aboutWorkingHoursRepository->create($aboutWorkingHoursDto);
                        Log::info('AboutUseCase:createOrUpdateAbout - created working_hour', ['index' => $index]);
                    } catch (\Throwable $e) {
                        Log::error('AboutUseCase:createOrUpdateAbout - failed creating working_hour', ['index' => $index, 'error' => $e->getMessage(), 'dto' => $aboutWorkingHoursDto->toArray()]);
                    }
                }
            }

            return DataSuccess(
                status: true,
                message: 'success'
            );
        } catch (\Throwable $th) {
            return DataFailed(
                status: false,
                message: $th->getMessage()
            );
        }
    }

    public function HandelAboutDetailResource($about, $viewType)
    {
        if ($viewType == ViewTypeEnum::DASHBOARD->value) {
            return DashboardAboutDetailResource::make($about);
        } elseif ($viewType == ViewTypeEnum::MOBILE->value) {
            return MobileAboutDetailResource::make($about);
        } else {
            return DashboardAboutDetailResource::make($about);
        }
    }
}
