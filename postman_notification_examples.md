# أمثلة Postman لاختبار نظام الإشعارات

## 1. إنشاء حجز جديد (يُرسل إشعار تلقائي)

### Endpoint: `POST /api/dashboard/create_booking`

**Headers:**

```
Content-Type: application/json
Authorization: Bearer YOUR_EMPLOYEE_TOKEN
```

**Body:**

```json
{
    "name": "أحمد محمد",
    "phone": "01234567890",
    "email": "ahmed@example.com",
    "date": "2025-01-16",
    "from_hour": "14:00",
    "employee_id": 1,
    "services": [1, 2]
}
```

**Expected Result:** سيتم إنشاء الحجز وإرسال إشعار تلقائي للموظف.

---

## 2. تأكيد حجز (تغيير الحالة إلى مؤكد)

### Endpoint: `POST /api/dashboard/update_booking`

**Headers:**

```
Content-Type: application/json
Authorization: Bearer YOUR_EMPLOYEE_TOKEN
```

**Body:**

```json
{
    "booking_id": 1,
    "status": 1
}
```

**Expected Result:** سيتم تأكيد الحجز وإرسال إشعار للموظف.

---

## 3. إلغاء حجز (تغيير الحالة إلى ملغي)

### Endpoint: `POST /api/dashboard/update_booking`

**Headers:**

```
Content-Type: application/json
Authorization: Bearer YOUR_EMPLOYEE_TOKEN
```

**Body:**

```json
{
    "booking_id": 1,
    "status": 2
}
```

**Expected Result:** سيتم إلغاء الحجز وإرسال إشعار للموظف.

---

## 4. إكمال خدمة (تغيير الحالة إلى مكتمل)

### Endpoint: `POST /api/dashboard/update_booking`

**Headers:**

```
Content-Type: application/json
Authorization: Bearer YOUR_EMPLOYEE_TOKEN
```

**Body:**

```json
{
    "booking_id": 1,
    "status": 3
}
```

**Expected Result:** سيتم إكمال الخدمة وإرسال إشعار للموظف.

---

## 5. حذف حجز

### Endpoint: `POST /api/dashboard/delete_booking`

**Headers:**

```
Content-Type: application/json
Authorization: Bearer YOUR_EMPLOYEE_TOKEN
```

**Body:**

```json
{
    "booking_id": 1
}
```

**Expected Result:** سيتم حذف الحجز وإرسال إشعار للموظف.

---

## 6. جلب إشعارات الموظف

### Endpoint: `POST /api/dashboard/fetch_notifications`

**Headers:**

```
Authorization: Bearer YOUR_EMPLOYEE_TOKEN
```

**Body (Optional):**

```json
{
    "limit": 20,
    "unread_only": false
}
```

**Expected Result:** قائمة بجميع إشعارات الموظف مع pagination.

**Response Example:**

```json
{
    "status": true,
    "message": "تم جلب الإشعارات بنجاح",
    "data": [
        {
            "id": 1,
            "title": "حجز جديد",
            "message": "تم إنشاء حجز جديد من العميل أحمد محمد",
            "type": "booking_created",
            "icon": "fas fa-calendar-plus",
            "color": "#10b981",
            "data": {
                "booking_id": 1,
                "customer_name": "أحمد محمد"
            },
            "is_read": false,
            "created_at": "2025-01-16 14:30:00",
            "read_at": null
        }
    ]
}
```

---

## 7. جلب عدد الإشعارات غير المقروءة

### Endpoint: `POST /api/dashboard/fetch_notifications_count`

**Headers:**

```
Authorization: Bearer YOUR_EMPLOYEE_TOKEN
```

**Expected Result:** عدد الإشعارات غير المقروءة.

**Response Example:**

```json
{
    "status": true,
    "message": "تم جلب العدد بنجاح",
    "data": {
        "unread_count": 5
    }
}
```

---

## 8. تحديد إشعار كمقروء

### Endpoint: `POST /api/dashboard/mark_notification_read`

**Headers:**

```
Authorization: Bearer YOUR_EMPLOYEE_TOKEN
```

**Body:**

```json
{
    "notification_id": 1
}
```

**Expected Result:** سيتم تحديد الإشعار كمقروء.

---

## 9. تحديد جميع الإشعارات كمقروءة

### Endpoint: `POST /api/dashboard/mark_all_notifications_read`

**Headers:**

```
Authorization: Bearer YOUR_EMPLOYEE_TOKEN
```

**Expected Result:** سيتم تحديد جميع الإشعارات كمقروءة.

---

## 10. حذف إشعار

### Endpoint: `POST /api/dashboard/delete_notification`

**Headers:**

```
Authorization: Bearer YOUR_EMPLOYEE_TOKEN
```

**Body:**

```json
{
    "notification_id": 1
}
```

**Expected Result:** سيتم حذف الإشعار.

---

## 11. حذف جميع الإشعارات المقروءة

### Endpoint: `POST /api/dashboard/delete_read_notifications`

**Headers:**

```
Authorization: Bearer YOUR_EMPLOYEE_TOKEN
```

**Expected Result:** سيتم حذف جميع الإشعارات المقروءة.

---

## أنواع الإشعارات المتاحة:

1. **BOOKING_CREATED** - إنشاء حجز جديد
2. **BOOKING_CONFIRMED** - تأكيد الحجز
3. **BOOKING_CANCELLED** - إلغاء الحجز
4. **BOOKING_FINISHED** - إكمال الخدمة
5. **SYSTEM_ALERT** - تنبيهات النظام
6. **PROMOTION** - العروض والخصومات
7. **REMINDER** - تذكيرات

## ملاحظات مهمة:

-   تأكد من أن الموظف مسجل دخول وحاصل على token صالح
-   تأكد من وجود خدمات وموظفين في قاعدة البيانات قبل الاختبار
-   يمكنك استخدام `php artisan tinker` لإنشاء بيانات تجريبية
-   الإشعارات تُرسل تلقائياً عند تنفيذ العمليات المذكورة أعلاه
