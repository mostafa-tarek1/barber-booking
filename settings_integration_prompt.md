# About API Integration Prompt

## Overview

You need to integrate two About endpoints with the frontend for a barber booking application. The system manages barbershop information including contact details, working hours, social media links, and location data.

## API Endpoints

### 1. Fetch About Information Endpoint

-   **URL**: `POST /dashboard/fetch-settings`
-   **Method**: POST
-   **Authentication**: Required (Employee middleware)
-   **Purpose**: Retrieve current barbershop information

#### Request Body

```json
{
    "paginate": false,
    "per_page": 10,
    "word": ""
}
```

#### Response Format

```json
{
    "success": true,
    "message": "about fetched successfully",
    "data": {
        "id": 1,
        "title_en": "Barber Shop",
        "title_ar": "صالون الحلاقة",
        "description_en": "Professional barber services",
        "description_ar": "خدمات حلاقة احترافية",
        "location_en": "123 Main Street",
        "location_ar": "123 الشارع الرئيسي",
        "lat": 25.2048,
        "lng": 55.2708,
        "image": "path/to/image.jpg",
        "working_hours": [
            {
                "from_time": "09:00:00",
                "to_time": "18:00:00",
                "days": {
                    "title": ["Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday"]
                }
            }
        ],
        "phones": ["+1234567890", "+0987654321"],
        "emails": ["info@barbershop.com", "contact@barbershop.com"],
        "socials": [
            {
                "id": 1,
                "key": "facebook",
                "value": "https://facebook.com/barbershop",
                "image": "path/to/facebook-icon.png"
            },
            {
                "id": 2,
                "key": "instagram",
                "value": "https://instagram.com/barbershop",
                "image": "path/to/instagram-icon.png"
            }
        ]
    }
}
```

### 2. Create/Update About Information Endpoint

-   **URL**: `POST /dashboard/create-or-update-setting`
-   **Method**: POST
-   **Authentication**: Required (Employee middleware)
-   **Purpose**: Create new or update existing barbershop information

#### Request Body

```json
{
    "about_id": 1, // Optional - only for updates
    "translations": {
        "title_en": "Barber Shop",
        "title_ar": "صالون الحلاقة",
        "description_en": "Professional barber services",
        "description_ar": "خدمات حلاقة احترافية",
        "location_en": "123 Main Street",
        "location_ar": "123 الشارع الرئيسي"
    },
    "lat": 25.2048,
    "lng": 55.2708,
    "image": "base64_encoded_image_or_file_path",
    "working_hours": [
        {
            "days": {
                "title": ["Saturday", "Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
                "from_time": "09:00",
                "to_time": "18:00"
            }
        }
    ],
    "phones": ["+1234567890", "+0987654321"],
    "emails": ["info@barbershop.com", "contact@barbershop.com"],
    "socials": [
        {
            "id": 1, // Optional - for updates
            "key": "facebook",
            "value": "https://facebook.com/barbershop",
            "image": "base64_encoded_image_or_file_path"
        },
        {
            "key": "instagram",
            "value": "https://instagram.com/barbershop",
            "image": "base64_encoded_image_or_file_path"
        }
    ]
}
```

#### Response Format

```json
{
    "success": true,
    "message": "About updated successfully",
    "data": {
        "id": 1,
        "title_en": "Barber Shop",
        "title_ar": "صالون الحلاقة",
        "description_en": "Professional barber services",
        "description_ar": "خدمات حلاقة احترافية",
        "location_en": "123 Main Street",
        "location_ar": "123 الشارع الرئيسي",
        "lat": 25.2048,
        "lng": 55.2708,
        "image": "path/to/image.jpg",
        "working_hours": [...],
        "phones": [...],
        "emails": [...],
        "socials": [...]
    }
}
```

## Data Structure Details

### Supported Social Media Keys
- `facebook` - Facebook page
- `instagram` - Instagram account
- `twitter` - Twitter account
- `whatsapp` - WhatsApp number
- `youtube` - YouTube channel
- `tiktok` - TikTok account
- `linkedin` - LinkedIn profile

### Fields Description
- `about_id`: About record ID (for updates)
- `translations`: Multilingual content (title, description, location)
- `lat/lng`: GPS coordinates for location
- `image`: Barbershop main image
- `working_hours`: Array of working time periods
- `phones`: Array of phone numbers
- `emails`: Array of email addresses
- `socials`: Array of social media links

## Frontend Integration Requirements

### 1. Settings Form Component

Create a form with the following fields:

-   **Tax Amount Input**: Number input for tax value
-   **Tax Type Select**: Dropdown with options:
    -   "Fixed Amount" (value: 1)
    -   "Percentage" (value: 2)
-   **Save Button**: To submit the form

### 2. API Service Functions

Implement these functions:

```javascript
// Fetch current settings
async function fetchSettings() {
    const response = await fetch("/dashboard/fetch-settings", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
        },
        body: JSON.stringify({
            paginate: false,
            per_page: 10,
            word: "",
        }),
    });
    return await response.json();
}

// Create or update settings
async function saveSettings(settingsData) {
    const response = await fetch("/dashboard/create-or-update-setting", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
        },
        body: JSON.stringify(settingsData),
    });
    return await response.json();
}
```

### 3. Form Validation

-   Tax amount must be a positive number
-   Tax type must be either 1 or 2
-   Show appropriate error messages for validation failures

### 4. UI/UX Requirements

-   Load existing settings when component mounts
-   Show loading states during API calls
-   Display success/error messages
-   Form should be responsive and user-friendly
-   Use Arabic labels for better user experience

### 5. Error Handling

Handle these scenarios:

-   Network errors
-   Authentication failures
-   Validation errors from server
-   Server errors (500, etc.)

### 6. Success Flow

-   After successful save, show success message
-   Optionally refresh the form with updated data
-   Update UI to reflect changes

## Implementation Notes

-   The endpoints require employee authentication
-   Both endpoints return JSON responses
-   The system supports both creating new settings and updating existing ones
-   Tax type determines how the tax amount is calculated (fixed vs percentage)
-   Use appropriate HTTP status codes for error handling

## Example Usage Flow

1. User opens settings page
2. System fetches current settings using `fetch-settings`
3. Form is populated with existing data
4. User modifies tax amount or type
5. User clicks save
6. System calls `create-or-update-setting` with new data
7. Success message is displayed
8. Form is updated with saved data

This integration will allow administrators to manage tax settings for the barber booking system effectively.
