<?php

use App\Modules\Rooms\Http\Controllers\Dashboard\Room\RoomController;
use App\Modules\Rooms\Http\Controllers\Dashboard\RoomAdditional\RoomAdditionalController;
use App\Modules\Rooms\Http\Controllers\Dashboard\RoomLocation\RoomLocationController;
use App\Modules\Rooms\Http\Controllers\Dashboard\RoomLocationMedia\RoomLocationMediaController;
use Illuminate\Support\Facades\Route;


Route::prefix('dashboard')->group(function () {
    Route::middleware('baseAuthMiddleware:employee')->group(function () {
        // ---------------- Rooms ----------------

        Route::post('fetch_rooms', [RoomController::class, 'fetchRooms']);
        Route::post('fetch_room_details', [RoomController::class, 'fetchRoomDetails']);
        Route::post('create_room', [RoomController::class, 'createRoom']);
        Route::post('update_room', [RoomController::class, 'updateRoom']);
        Route::post('delete_room', [RoomController::class, 'deleteRoom']);
        // ---------------- Room Locations ----------------

        Route::post('fetch_room_locations', [RoomLocationController::class, 'fetchRoomLocations']);
        Route::post('fetch_room_location_details', [RoomLocationController::class, 'fetchRoomLocationDetails']);
        Route::post('create_room_location', [RoomLocationController::class, 'createRoomLocation']);
        Route::post('update_room_location', [RoomLocationController::class, 'updateRoomLocation']);
        Route::post('delete_room_location', [RoomLocationController::class, 'deleteRoomLocation']);
        // ---------------- Room Location Media ----------------

        Route::post('fetch_room_location_medias', [RoomLocationMediaController::class, 'fetchRoomLocationMedias']);
        Route::post('fetch_room_location_media_details', [RoomLocationMediaController::class, 'fetchRoomLocationMediaDetails']);
        Route::post('create_room_location_media', [RoomLocationMediaController::class, 'createRoomLocationMedia']);
        Route::post('update_room_location_media', [RoomLocationMediaController::class, 'updateRoomLocationMedia']);
        Route::post('delete_room_location_media', [RoomLocationMediaController::class, 'deleteRoomLocationMedia']);

        // ---------------- Room Additionals ----------------
        Route::post('fetch_room_additionals', [RoomAdditionalController::class, 'fetchRoomAdditionals']);
        Route::post('fetch_room_additional_details', [RoomAdditionalController::class, 'fetchRoomAdditionalDetails']);
        Route::post('create_room_additional', [RoomAdditionalController::class, 'createRoomAdditional']);
        Route::post('update_room_additional', [RoomAdditionalController::class, 'updateRoomAdditional']);
        Route::post('delete_room_additional', [RoomAdditionalController::class, 'deleteRoomAdditional']);
    });
});
