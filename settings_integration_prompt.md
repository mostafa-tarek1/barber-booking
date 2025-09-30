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
                    "title": [
                        "Saturday",
                        "Sunday",
                        "Monday",
                        "Tuesday",
                        "Wednesday",
                        "Thursday",
                        "Friday"
                    ]
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
                "title": [
                    "Saturday",
                    "Sunday",
                    "Monday",
                    "Tuesday",
                    "Wednesday",
                    "Thursday",
                    "Friday"
                ],
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

-   `facebook` - Facebook page
-   `instagram` - Instagram account
-   `twitter` - Twitter account
-   `whatsapp` - WhatsApp number
-   `youtube` - YouTube channel
-   `tiktok` - TikTok account
-   `linkedin` - LinkedIn profile

### Fields Description

-   `about_id`: About record ID (for updates)
-   `translations`: Multilingual content (title, description, location)
-   `lat/lng`: GPS coordinates for location
-   `image`: Barbershop main image
-   `working_hours`: Array of working time periods
-   `phones`: Array of phone numbers
-   `emails`: Array of email addresses
-   `socials`: Array of social media links

## Frontend Integration Requirements

### 1. About Information Form Component

Create a comprehensive form with the following sections:

#### Basic Information Section

-   **Title (English)**: Text input for English title
-   **Title (Arabic)**: Text input for Arabic title
-   **Description (English)**: Textarea for English description
-   **Description (Arabic)**: Textarea for Arabic description
-   **Location (English)**: Text input for English location
-   **Location (Arabic)**: Text input for Arabic location
-   **Main Image**: File upload or base64 image input

#### Location Section

-   **Latitude**: Number input for GPS latitude
-   **Longitude**: Number input for GPS longitude

#### Working Hours Section

-   **Working Hours**: Dynamic array of working periods
    -   **Days**: Multi-select for days of the week
    -   **From Time**: Time picker for start time
    -   **To Time**: Time picker for end time
    -   **Add/Remove**: Buttons to add or remove working periods

#### Contact Information Section

-   **Phone Numbers**: Dynamic array of phone inputs
    -   **Add/Remove**: Buttons to add or remove phone numbers
-   **Email Addresses**: Dynamic array of email inputs
    -   **Add/Remove**: Buttons to add or remove email addresses

#### Social Media Section

-   **Social Media Links**: Dynamic array of social media entries
    -   **Platform**: Dropdown with social media options (Facebook, Instagram, Twitter, WhatsApp, YouTube, TikTok, LinkedIn)
    -   **URL**: Text input for social media URL
    -   **Icon**: File upload for social media icon
    -   **Add/Remove**: Buttons to add or remove social media entries

#### Action Buttons

-   **Save Button**: To submit the form
-   **Cancel Button**: To reset form
-   **Preview Button**: To preview changes

### 2. API Service Functions

Implement these functions:

```javascript
// Fetch current about information
async function fetchAboutInfo() {
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

// Create or update about information
async function saveAboutInfo(aboutData) {
    const response = await fetch("/dashboard/create-or-update-setting", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Authorization: "Bearer " + token,
        },
        body: JSON.stringify(aboutData),
    });
    return await response.json();
}
```

### 3. Form Validation

-   **Required Fields**: Title (both languages), Description (both languages)
-   **Email Validation**: All email addresses must be valid
-   **Phone Validation**: Phone numbers should follow international format
-   **URL Validation**: Social media URLs must be valid
-   **Time Validation**: Working hours end time must be after start time
-   **Image Validation**: Images must be valid format and size
-   **GPS Validation**: Latitude (-90 to 90), Longitude (-180 to 180)

### 4. UI/UX Requirements

-   **Multi-language Support**: Form should support both English and Arabic
-   **Responsive Design**: Form should work on desktop, tablet, and mobile
-   **Loading States**: Show loading indicators during API calls
-   **Success/Error Messages**: Display appropriate feedback messages
-   **Dynamic Sections**: Working hours, phones, emails, and socials should be dynamically manageable
-   **Image Preview**: Show image previews before upload
-   **Form Sections**: Organize form into logical sections with clear headings
-   **RTL Support**: Proper right-to-left layout for Arabic content

### 5. Error Handling

Handle these scenarios:

-   **Network Errors**: Connection timeouts, server unavailable
-   **Authentication Failures**: Token expired, unauthorized access
-   **Validation Errors**: Field validation failures from server
-   **File Upload Errors**: Image upload failures
-   **Server Errors**: 500, 404, etc.

### 6. Success Flow

-   **Success Message**: Display confirmation after successful save
-   **Form Reset**: Option to reset form or keep changes
-   **Data Refresh**: Reload form with updated data
-   **Navigation**: Option to navigate to other sections

## Implementation Notes

-   **Authentication**: Endpoints require employee authentication
-   **Multilingual**: Support for English and Arabic content
-   **File Handling**: Support for both file uploads and base64 encoded images
-   **Dynamic Arrays**: Working hours, phones, emails, and socials are dynamic arrays
-   **GPS Coordinates**: Location data includes latitude and longitude
-   **Social Media**: Support for multiple social media platforms with custom icons

## Example Usage Flow

1. **Load Page**: User opens about information page
2. **Fetch Data**: System fetches current about information using `fetch-settings`
3. **Populate Form**: Form is populated with existing data
4. **Edit Information**: User modifies any section (basic info, location, working hours, contact, social media)
5. **Add/Remove Items**: User can add or remove working hours, phones, emails, or social media entries
6. **Upload Images**: User can upload main image and social media icons
7. **Save Changes**: User clicks save button
8. **API Call**: System calls `create-or-update-setting` with updated data
9. **Success Feedback**: Success message is displayed
10. **Form Update**: Form is updated with saved data

## Advanced Features

### Image Handling

-   **Drag & Drop**: Support drag and drop for image uploads
-   **Image Compression**: Compress images before upload
-   **Multiple Formats**: Support JPEG, PNG, WebP formats
-   **Size Limits**: Enforce maximum file sizes

### Working Hours Management

-   **Multiple Periods**: Support multiple working time periods per day
-   **Day Selection**: Visual day selector with checkboxes
-   **Time Validation**: Ensure end time is after start time
-   **Copy Hours**: Option to copy working hours to other days

### Social Media Integration

-   **Platform Icons**: Pre-loaded icons for each social media platform
-   **URL Validation**: Validate URLs for each platform
-   **Preview Links**: Show preview of social media links
-   **Bulk Import**: Option to import multiple social media links

This integration will allow administrators to manage comprehensive barbershop information including contact details, working hours, location, and social media presence.
