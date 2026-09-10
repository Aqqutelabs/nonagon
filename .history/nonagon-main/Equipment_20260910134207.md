# Equipment Feature Breakdown

This document describes the equipment domain of the application as implemented in the Laravel project. It is based on the code in the models, migrations, controllers, routes, and access logic.

## 1. Product overview

The system is an enterprise asset management platform focused on tracking and managing physical equipment across an organization.

The core domain is built around:
- Organizations
- Base / Site / Plant / Unit hierarchy
- Users and role-based access
- Equipment records
- Equipment category and status systems
- Maintenance and work-order operations
- Subscription and feature controls

In practical terms, the application is designed to help a company manage its fleet or plant assets, monitor equipment health, assign access by location, and track maintenance lifecycle.

---

## 2. Core equipment model

The main equipment entity is defined in:
- `app/Models/Equipment.php`
- `database/migrations/2026_02_10_063707_create_equipment_table.php`

The equipment record stores:
- `id` as UUID
- `name`
- `aid` (Asset ID)
- `type`
- `reg_no` (registration number)
- `brand`
- `model`
- `emd` (serial or VIN-like identifier)
- `manufacture_year`
- `description`
- `industry`
- `city`
- `last_maintenance`
- `organization_id`
- `base_id`
- `site_id`
- `plant_id`
- `unit_id`
- `equipment_category_id`
- `equipment_subcategory_id`
- `equipment_type_id`
- `equipment_status_id`
- timestamps

This is a rich asset registry, not just a simple “equipment list.” It is designed to support operational context and lifecycle management.

### Equipment relationships
The model establishes strong relationships with:
- category
- status
- base
- site
- plant
- unit
- organization
- depreciation profile
- value snapshot
- current value record
- maintenance work order
- breakdown incidents
- maintenance schedules
- maintenance logs

This means equipment is treated as a central entity linked to financial, operational, and maintenance data.

---

## 3. Hierarchical location system

One of the strongest features of this app is the organization/location hierarchy.

The system is designed around:
- Organization
- Base
- Site
- Plant
- Unit

This hierarchy allows equipment to be assigned and filtered by its physical and operational location.

### Why it matters
It supports:
- location-based access restrictions
- operator assignment by region or facility
- equipment filtering by physical area
- organizational visibility controls

### Access logic
Users are linked to locations through `location_users` records. This is used in:
- `app/Models/User.php`
- `app/Helpers/AccessHelper.php`
- `app/Http/Controllers/Equipment/EquipmentController.php`

Users can be assigned one or more of:
- base
- site
- plant
- unit

Access is then evaluated by checking whether the equipment belongs to a permitted location.

### Default setup during registration
When a new organization is created, the system automatically creates default location records:
- default base
- default site
- default plant
- default unit

This ensures immediate usage without manual setup.

---

## 4. Organization and tenant model

The application is built around the idea of a tenant organization.

The `Organization` model supports:
- users
- equipment
- subscription linkage
- feature flags
- feature limits

The `Organization` model includes methods such as:
- `hasFeature(string $featureKey): bool`
- `featureLimit(string $featureKey): ?int`

This is the foundation for a SaaS model where organization-level access is managed by subscription and feature availability.

---

## 5. User roles and access control

The user model includes role-based logic and location-based permission evaluation.

Relevant features include:
- `role` values like `owner`, `operator`, and possibly `admin`
- `organization()` relationship
- `baseIds()`, `siteIds()`, `plantIds()`, and `unitIds()` methods
- `canAccessEquipment($equipment)` logic

### Role behavior
- Owner users have broad access by default.
- Non-owner users are checked against their assigned locations.
- Access is validated against `base_id`, `site_id`, `plant_id`, and `unit_id` on the equipment record.

This is a practical authorization model for plant operations and asset visibility restrictions.

---

## 6. Feature and subscription features

The app includes a structured feature system:
- `app/Constants/Features.php`

Supported feature keys include:
- `equipment`
- `maintenance`
- `lease`
- `equipment_limit`
- `operator_limit`
- `site_limit`

This suggests the app supports a tiered subscription or enterprise packaging model.

### Feature usage patterns
The codebase includes middleware like:
- `EnsureFeatureEnabled`
- `EnsureSubscriptionActive`
- `EnsureOrganizationActive`

These middleware classes indicate the app restricts access to some operations if the organization lacks required subscription or feature privileges.

This is a significant SaaS-oriented capability and a strong sign that the app was designed beyond a local asset tracker.

---

## 7. Equipment categories and classification

The project includes dedicated models for:
- EquipmentCategory
- EquipmentSubcategory
- EquipmentType
- EquipmentStatus

These are important for organizing and filtering equipment by:
- asset family
- subclass
- equipment type
- operational state

### Category and status filtering
The equipment controller filters by:
- `status`
- `category`
- `unit`
- `critical`

This means the app supports real operational classification such as:
- critical equipment
- equipment under maintenance
- equipment grouped by category or site unit

---

## 8. Search and filtering capabilities

The main equipment listing includes advanced filtering logic in:
- `app/Http/Controllers/Equipment/EquipmentController.php`

It supports:
- text search across name, asset ID, brand, and model
- filtering by status
- filtering by category
- filtering by unit
- critical-only filtering

The project also includes a `search` method for fast equipment lookup using user input.

This is a real operational feature rather than a simple index page.

---

## 9. Equipment dashboard and listing

The equipment controller prepares a full listing view with:
- equipment collection
- category list
- status list
- location tree
- summary stats

### Stats included
The controller calculates:
- total assets
- number currently under maintenance
- number of operators in organization

This supports a management dashboard view that helps operations teams understand current health and usage at a glance.

### Location tree data
The system loads bases with nested sites, plants, and units, and even includes counts on units.

This gives the UI a structured tree view for navigation and filtering.

---

## 10. Maintenance management features

The project includes direct maintenance domain models and migration files such as:
- `MaintenanceSchedule`
- `MaintenanceWorkOrder`
- `MaintenanceWorkOrderTask`
- `MaintenanceWorkOrderAttachment`
- `EquipmentMaintenanceLog`
- `EquipmentMeter`
- `EquipmentMeterReading`
- `EquipmentMeterSummary`

This indicates the app is designed to support:
- maintenance schedules
- work orders
- tasks within work orders
- asset maintenance history
- attachment tracking
- meter-based maintenance triggers

### Maintenance lifecycle
The app clearly tracks ongoing maintenance across the lifecycle of equipment, which is a key enterprise asset management behavior.

---

## 11. Work order and task tracking

The app includes a maintenance work order system, which is one of the most important operational features.

The model set suggests:
- a work order belongs to an equipment record
- tasks are associated with a work order
- attachments can be uploaded
- maintenance logs capture what was done

This means the application can support:
- scheduled inspections
- corrective maintenance
- preventive maintenance
- task tracking
- documentation of maintenance activity

---

## 12. Metering and performance tracking

The database includes equipment meter models and readings:
- `equipment_meters`
- `equipment_meter_readings`
- `equipment_meter_summaries`

This is a strong sign the app supports:
- runtime or usage meters
- reading history
- summary calculations
- KPI style monitoring for equipment usage and service intervals

This makes the system more than a static inventory platform; it supports condition and utilization tracking.

---

## 13. Financial and asset valuation

The app includes valuation and depreciation-related models:
- `EquipmentDepreciationProfile`
- `EquipmentValueSnapshot`
- `EquipmentValueCurrent`

These features suggest the system can support:
- depreciation calculation
- asset valuation snapshots
- current market or book value tracking
- historical value records

This is a valuable capability for asset-heavy organizations that need financial visibility on equipment value.

---

## 14. Breakdowns and incident tracking

The system includes a breakdown incident model:
- `BreakdownIncident`

This supports recording:
- equipment failures
- downtime events
- operational disruption tracking

This is a strong operational maintenance feature and likely connects to maintenance, audits, and reporting.

---

## 15. Auditability and event logs

The app includes an equipment audit log model:
- `EquipmentAuditLog`

This indicates the system is intended to maintain historical changes for assets, including:
- modifications to equipment data
- maintenance events
- operational changes
- traceability for compliance or troubleshooting

This is important for enterprise environments.

---

## 16. Photos, assemblies, and component structure

The app includes supporting models like:
- `EquipmentPhoto`
- `EquipmentAssembly`
- `EquipmentAssemblyItem`
- `PartMaster`
- `AssemblyTemplate`
- `AssemblyTemplateItem`

This means the app is capable of modeling:
- equipment photos
- assemblies and subassemblies
- spare-part definitions
- template-driven component assembly

This is a strong sign that the system supports not just stand-alone equipment records, but equipment composition and part-level management.

---

## 17. Invitation and user onboarding

The auth flow includes invite functionality:
- `sendInvite`
- `acceptInvite`
- `completeInvite`

This enables:
- onboarding staff into an organization
- assigning a user to a specific location (base/site/plant/unit)
- operator provisioning without a full administrative setup

This is a practical operational feature for multi-user asset teams.

---

## 18. Email verification and security flow

Routes and middleware include email verification support:
- `EnsureEmailIsVerified`
- verification endpoints in routes

This indicates a secure account lifecycle where users may need to verify email before using the platform.

---

## 19. Admin and reporting features

The route file includes pages such as:
- maintenance
- users
- reports
- settings
- profile
- account

This indicates the platform includes an admin dashboard and reporting layer, even if some views are still scaffolded or partially implemented.

---

## 20. What is clearly implemented versus still partial

### Clearly implemented areas
- organization and tenant model
- equipment registry
- location hierarchy
- equipment status/category filtering
- authorization by location and role
- maintenance workflow models
- valuation/depreciation models
- subscriptions/feature gates
- audit/event logging

### Likely partial or scaffolded areas
- admin pages and UI views
- some routes are commented out
- some auth flows appear partially disabled
- feature usage may not yet be fully connected to all UI pages

This is typical of a growing platform where core backend structures exist before every frontend page is fully implemented.

---

## 21. Main strengths of this equipment system

The equipment domain is strong because it combines:
- inventory and asset tracking
- operational location hierarchy
- user-based access control
- maintenance management
- value and depreciation tracking
- support for failures, logs, and audits
- SaaS feature gating

This gives the application a serious enterprise asset management feel.

---

## 22. Recommended strategic interpretation

If this project is being developed as a business product, the equipment module is the foundation for a full asset lifecycle platform. It is not merely a CRUD list; it is designed to manage:
- asset existence
- asset placement
- asset health
- maintenance processes
- asset value
- compliance and auditability
- operational access control

---

## 23. Final assessment

The equipment feature set is broad, structured, and enterprise-oriented. It is one of the more complete domain modules in the application and forms the center of the platform’s value.

The application is best understood as a multi-tenant industrial asset and maintenance management system with a strong backend architecture and a clear path toward full product maturity.
