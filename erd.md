# Database ERD of Multi-Vendor Booking Platform

```mermaid
erDiagram
    USERS ||--o| VENDOR_PROFILES : "has (if vendor)"
    VENDOR_PROFILES ||--o{ SERVICES : "offers"
    SERVICES ||--o{ SLOTS : "has"
    SLOTS ||--o| BOOKINGS : "booked as"
    USERS ||--o{ BOOKINGS : "makes"
    BOOKINGS ||--o| PAYMENTS : "paid via"
    BOOKINGS ||--o| REVIEWS : "reviewed as"
    USERS ||--o{ REVIEWS : "writes"
    SERVICES ||--o{ REVIEWS : "receives"
    USERS ||--o{ VENDOR_PROFILES : "approved by (admin)"

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        string phone
        string role
        timestamp deleted_at
    }

    VENDOR_PROFILES {
        bigint id PK
        bigint fk_vendor_id FK
        string business_name
        boolean is_approved
        bigint approved_by FK
        timestamp approved_at
        timestamp deleted_at
    }

    SERVICES {
        bigint id PK
        bigint fk_vendor_profile_id FK
        string title
        text description
        string category
        string location
        decimal price_per_slot
        boolean is_active
        timestamp deleted_at
    }

    SLOTS {
        bigint id PK
        bigint fk_service_id FK
        datetime start_time
        datetime end_time
        string status
        timestamp deleted_at
    }

    BOOKINGS {
        bigint id PK
        bigint fk_slot_id FK "unique"
        bigint fk_customer_id FK
        string status
        decimal amount
        timestamp deleted_at
    }

    PAYMENTS {
        bigint id PK
        bigint fk_booking_id FK "unique"
        string stripe_payment_intent_id
        string status
        decimal amount
        decimal refunded_amount
        timestamp deleted_at
    }

    REVIEWS {
        bigint id PK
        bigint fk_booking_id FK "unique"
        bigint fk_customer_id FK
        bigint fk_service_id FK
        tinyint rating
        text comment
    }
```

## How to read this

- `||--o{` means "one to many", one `VENDOR_PROFILES` row can have many `SERVICES` rows.
- `||--o|` means "one to one", one `BOOKINGS` row has exactly one `PAYMENTS` row.
- `FK "unique"` on a foreign key means that column also has a unique constraint, this is what makes `slot_id` on `bookings` guarantee one slot can never have two bookings.

## Relationship summary

- **users → vendor_profiles**: one-to-one. Only users with `role = vendor` have a row here.
- **vendor_profiles → services**: one-to-many. One vendor can list many venues/services.
- **services → slots**: one-to-many. One listing has many bookable time slots.
- **slots → bookings**: one-to-one. Enforced by a unique constraint on `slot_id`, this is the database-level safety net against double-booking.
- **users → bookings**: one-to-many. A customer can have many bookings.
- **bookings → payments**: one-to-one. Each booking has exactly one payment record.
- **bookings → reviews**: one-to-one. You can only review a booking you actually made, once.
- **users → vendor_profiles (approved_by)**: a second relationship from `users`, representing which admin approved a given vendor.

## Why this is normalized

No table stores data that belongs to another table. `bookings.amount` is the one intentional exception, it's a price snapshot at time of booking, not a normalization mistake, since the live price lives in `services.price_per_slot` and can change independently.
