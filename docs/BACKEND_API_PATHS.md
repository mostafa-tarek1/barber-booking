# Backend API Paths (لربط الفرونت إند)

## شكل الـ Response الموحد

كل الـ endpoints ترجع بالشكل:

```json
{
  "status": 200,
  "message": "رسالة توضيحية",
  "data": { ... }
}
```

- **`status`**: رقم (مثلاً 200 للنجاح، 400/422 للـ validation، 404 لعدم الوجود).
- **`message`**: نص يوضح النتيجة أو الخطأ.
- **`data`**: المحتوى (كائن، مصفوفة، أو null).

---

## Base URL في الفرونت إند (متغير بيئة واحد)

الفرونت إند بياخد الـ base URL من **متغير بيئة واحد** عشان ما تحتاجش تغيّره في أكتر من مكان.

| | |
|--|--|
| **المتغير** | `VITE_API_BASE_URL` |
| **أين تضعه** | تطوير محلي: `.env` أو `.env.local` (انظر `.env.example`) — عند الرفع: إعدادات الـ hosting (Environment Variables) |
| **القيمة محلياً** | `http://127.0.0.1:8000` |
| **القيمة على السيرفر** | `https://api.yourdomain.com` **(بدون** `/dashboard` في الآخر) |

كل الـ API calls والصور بتستخدم هذا الرابط من ملف `src/lib/config.ts`؛ لو غيّرت القيمة في مكان واحد (الـ env)، المشروع كله يتبعها.

الـ paths في هذا الملف (مثل `/dashboard/login`, `/api/dashboard/...`) تُضاف لهذا الـ base من طرف الفرونت.

---

## Auth (Dashboard)

| Method | Path | ملاحظات |
|--------|------|---------|
| POST | `/dashboard/login` | Body: `{ credential, password }` |
| POST | `/dashboard/logout` | مع الـ token في الـ headers |

---

## Bookings (نسبية لـ base = `/dashboard`)

| Method | Path |
|--------|------|
| POST | `/dashboard/create_booking` |
| POST | `/dashboard/fetch_bookings` |
| POST | `/dashboard/update_booking` |
| POST | `/dashboard/fetch_booking_details` |
| POST | `/dashboard/delete_booking` |
| POST | `/dashboard/check_availability` |

---

## Statistics

| Method | Path |
|--------|------|
| POST | `/dashboard/fetch_dashboard_statistics` |
| POST | `/dashboard/fetch_statistics` |
| POST | `/dashboard/fetch_daily_activity` |
| POST | `/dashboard/fetch_monthly_activity` |
| POST | `/dashboard/fetch_top_services` |
| POST | `/dashboard/fetch_top_barbers` |

---

## Hashtags

| Method | Path |
|--------|------|
| POST | `/dashboard/create_hashtag` |
| POST | `/dashboard/fetch_hashtags` |
| POST | `/dashboard/fetch_hashtag_details` |
| POST | `/dashboard/update_hashtag` |
| POST | `/dashboard/delete_hashtag` |

---

## Categories

| Method | Path |
|--------|------|
| POST | `/dashboard/create_category` |
| POST | `/dashboard/fetch_categories` |
| POST | `/dashboard/fetch_category_details` |
| POST | `/dashboard/update_category` |
| POST | `/dashboard/delete_category` |

---

## Blog

| Method | Path |
|--------|------|
| POST | `/dashboard/fetch_blogs` |
| POST | `/dashboard/fetch_blog_details` |
| POST | `/dashboard/create_blog` |
| POST | `/dashboard/update_blog` |
| POST | `/dashboard/delete_blog` |

---

## About Us

| Method | Path |
|--------|------|
| POST | `/dashboard/create-or-update-about_us` |
| POST | `/dashboard/fetch-about_us` |
| POST | `/dashboard/delete_about_us_images` |

---

## Slider

| Method | Path |
|--------|------|
| POST | `/dashboard/create_slider` |
| POST | `/dashboard/fetch_sliders` |
| POST | `/dashboard/fetch_slider_details` |
| POST | `/dashboard/update_slider` |
| POST | `/dashboard/delete_slider` |

---

## Settings

| Method | Path |
|--------|------|
| POST | `/dashboard/fetch-settings` |
| POST | `/dashboard/create-or-update-setting` |

---

## Terms

| Method | Path |
|--------|------|
| POST | `/dashboard/fetch_terms` |
| POST | `/dashboard/create-or-update_terms` |

---

## Privacy

| Method | Path |
|--------|------|
| POST | `/dashboard/fetch_privacy` |
| POST | `/dashboard/create-or-update_privacy` |

---

## FAQ

| Method | Path |
|--------|------|
| POST | `/dashboard/fetch_faqs` |
| POST | `/dashboard/fetch_faq_details` |
| POST | `/dashboard/create_faq` |
| POST | `/dashboard/update_faq` |
| POST | `/dashboard/delete_faq` |

---

## Services

| Method | Path |
|--------|------|
| POST | `/dashboard/fetch_services` |
| POST | `/dashboard/fetch_service_details` |
| POST | `/dashboard/create_service` |
| POST | `/dashboard/update_service` |
| POST | `/dashboard/delete_service` |

---

## Employees

| Method | Path |
|--------|------|
| POST | `/dashboard/fetch_employees` |
| POST | `/dashboard/fetch_employee_details` |
| POST | `/dashboard/create_employee` |
| POST | `/dashboard/update_employee` |
| POST | `/dashboard/delete_employee` |

---

## Users (Dashboard)

| Method | Path |
|--------|------|
| POST | `/dashboard/create_user` |
| POST | `/dashboard/delete_user` |
| POST | `/dashboard/fetch_users` |
| POST | `/dashboard/fetch_user_details` |
| POST | `/dashboard/update_user` |
| POST | `/dashboard/force_logout` |
| POST | `/dashboard/block_user` |
| POST | `/dashboard/unblock_user` |
| POST | `/dashboard/toggle_approval` |

---

## Contact Us (Dashboard)

| Method | Path |
|--------|------|
| POST | `/dashboard/fetch_contact_us` |
| POST | `/dashboard/fetch_contact_us_details` |
| POST | `/dashboard/delete_contact_us` |

(ملاحظة: في الـ API للموبايل يوجد `POST /api/create_contact_us`.)

---

## Notifications (base = `/api/dashboard`)

| Method | Path |
|--------|------|
| POST | `/api/dashboard/fetch_notifications` |
| POST | `/api/dashboard/fetch_notifications_count` |
| POST | `/api/dashboard/mark_notification_read` |
| POST | `/api/dashboard/mark_all_notifications_read` |
| POST | `/api/dashboard/delete_notification` |
| POST | `/api/dashboard/delete_read_notifications` |
| POST | `/api/dashboard/notifications/send` |
| GET | `/api/dashboard/notifications/types` |

---

## CORS و Credentials

- الـ CORS مضبوط ليقبل: `http://localhost:5173`, `http://127.0.0.1:5173`, و `FRONTEND_URL` من `.env`.
- `supports_credentials` = `true` للـ cookies/auth.

## CSRF (Sanctum)

- **GET** `/sanctum/csrf-cookie` — يفضّل استدعاؤها قبل الطلبات اللي تحتاج session/cookies.
- الـ stateful domains تشمل: `localhost:5173`, `127.0.0.1:5173`.

## Headers المقترحة من الفرونت

- `X-Requested-With: XMLHttpRequest`
- `Accept: application/json`
- `Content-Type: application/json`
- `Authorization: Bearer {token}` عند الحاجة
