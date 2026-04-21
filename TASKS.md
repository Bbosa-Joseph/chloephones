# ChloePhones — Full Rebuild Master Plan

---

## TERMINOLOGY & LOCATION HIERARCHY

```
branches  ← TOP-LEVEL physical locations (e.g. "Kampala Branch", "Entebbe Branch")
  └── stores  ← sub-divisions inside a branch (e.g. "Phones Counter", "Accessories Counter")
```

| Table | Level | Meaning | FK |
|---|---|---|---|
| `branches` | Top | Physical building / city location | — |
| `stores` | Sub | Counter / department inside a branch | `branch_id FK→branches` |

| Old deleted model | New model | Table |
|---|---|---|
| `WarehouseModel` (deleted) | `BranchModel` | `branches` |
| `StoreModel` (deleted) | `StoreModel` | `stores` (new meaning) |

**Staff are assigned to branches** (the physical place they report to).  
**Sales/orders happen at store level** (the counter they serve at).  
**Purchases/stock arrive at branch level** first, then transferred to stores.

---

## CONTROLLER HIERARCHY & AUTH FLOW

```
BaseController          (abstract — session, helpers, redirectIfAuthenticated)
 ├── Auth               PUBLIC: login, logout, forgot-password, reset-password
 └── AppController      (abstract — reads session, loads permissions, render(), hasPermission(), deny())
      └── [all protected controllers below]
```

### How Auth Works (end to end)
1. User hits any protected URL → `AuthFilter` runs first
2. `AuthFilter` checks `session('logged_in') === true`
   - YES → pass through to controller
   - NO  → check `remember_me` cookie → find user by SHA-256(token) in DB
     - Found → restore session, roll token, pass through
     - Not found → flash "Please log in" → redirect to `auth/login`
3. `Auth::authenticate()` → validates → `UserModel::findForAuth(email)` (bypasses hiddenFields)
   → `password_verify` → set session with `user_id, username, email, first_name, last_name, permissions[]`
   → optionally write `remember_me` cookie (raw 32-byte hex) + store SHA-256(raw) in DB
4. `AppController::initController()` reads session into `$this->currentUser` + `$this->permissions[]`
5. Every protected action calls `$this->hasPermission('module.action')` before doing anything
6. Super Admin (role id=1) bypasses permission checks — `AppController` checks `$this->isSuperAdmin()`

---

## DATABASE SCHEMA — TABLES & RELATIONS

### AUTH / RBAC
```
users
  id PK, username, email, password(bcrypt), first_name, last_name,
  phone, gender, avatar, is_active(bool), last_login_at,
  reset_hash, reset_expires_at, remember_token,
  created_at, updated_at

roles
  id PK, name(unique), description, is_active, created_at, updated_at

permissions
  id PK, name(unique), module, description

role_permissions  [pivot]
  role_id FK→roles, permission_id FK→permissions
  PK(role_id, permission_id)

user_roles  [pivot]
  user_id FK→users, role_id FK→roles
  PK(user_id, role_id)

login_attempts
  id PK, ip_address, email, attempted_at(DATETIME)
  INDEX(ip_address, email, attempted_at)
```

### LOCATIONS
```
branches  (TOP-LEVEL physical location — building / city site)
  id PK, name, address, phone, email, is_active, created_at, updated_at

stores  (sub-division inside a branch — counter / department)
  id PK, branch_id FK→branches, name, description, phone, is_active,
  created_at, updated_at

branch_users  [pivot — staff assigned to a branch]
  branch_id FK→branches, user_id FK→users
  PK(branch_id, user_id)
```

### COMPANY
```
company_settings  (singleton, always id=1)
  id PK, company_name, address, phone, email, website,
  country, currency, currency_symbol,
  tax_rate(decimal 6,2), service_charge_rate(decimal 6,2),
  logo, footer_message, updated_at
```

### CATALOGUE
```
categories
  id PK, name, parent_id FK→categories(nullable, self-ref),
  description, is_active, deleted_at

brands
  id PK, name, description, is_active, deleted_at

attributes
  id PK, name, is_active

attribute_values
  id PK, attribute_id FK→attributes, value
```

### PRODUCTS
```
products
  id PK, name, sku(unique), category_id FK→categories,
  brand_id FK→brands(nullable),
  description(TEXT), cost_price(decimal 12,2), sell_price(decimal 12,2),
  inventory_method ENUM(bulk,serialized),
  is_active, created_by FK→users, deleted_at, created_at, updated_at

product_attributes  [pivot — product-level attributes]
  product_id FK→products, attribute_value_id FK→attribute_values
  PK(product_id, attribute_value_id)

product_variants
  id PK, product_id FK→products, variant_sku(unique nullable),
  cost_price(decimal 12,2), sell_price(decimal 12,2), is_active,
  created_at, updated_at

variant_attributes  [pivot — which attribute values define this variant]
  variant_id FK→product_variants, attribute_value_id FK→attribute_values
  PK(variant_id, attribute_value_id)

product_items  (serialized / IMEI units — one row = one physical device)
  id PK, product_id FK→products, variant_id FK→product_variants(nullable),
  imei(varchar 60, nullable unique),
  branch_id FK→branches(nullable),  ← receiving / warehouse location
  store_id FK→stores(nullable),     ← retail counter location
  CHECK: exactly one of branch_id / store_id must be non-null
  sell_price(decimal 12,2), cost_price(decimal 12,2),
  status ENUM(in_stock, sold, transferred, damaged, returned),
  date_received(date), created_by FK→users, created_at, updated_at
```

### STOCK
```
stock_levels  (bulk qty — one row per product+variant+location)
  id PK, product_id FK→products, variant_id FK→product_variants(nullable),
  branch_id FK→branches(nullable),  ← qty held at branch level (received, not yet distributed)
  store_id FK→stores(nullable),     ← qty held at store/counter level
  CHECK: exactly one of branch_id / store_id must be non-null
  qty_on_hand(int), reorder_level(int default 0), updated_at

stock_movements  (full audit log of every qty change)
  id PK, product_id FK→products, variant_id(nullable),
  product_item_id FK→product_items(nullable),
  type ENUM(purchase,sale,transfer_in,transfer_out,adjustment,return,damaged),
  qty(int signed),
  from_type ENUM(branch,store)(nullable), from_id(nullable),
  to_type ENUM(branch,store)(nullable),   to_id(nullable),
  reference_type(varchar — 'order'|'purchase'|'return'), reference_id(nullable),
  note(text nullable), moved_by FK→users, moved_at(datetime default NOW)
```

### SUPPLIERS & PURCHASES
```
suppliers
  id PK, name, contact_person, email, phone, address,
  is_active, deleted_at, created_at

purchases
  id PK, reference_no(unique), supplier_id FK→suppliers(nullable),
  branch_id FK→branches,  ← purchases received at branch level
  total_amount, paid_amount,
  payment_status ENUM(unpaid,partial,paid),
  note, purchased_by FK→users, purchased_at(datetime), created_at

purchase_items
  id PK, purchase_id FK→purchases, product_id FK→products,
  variant_id FK→product_variants(nullable),
  product_item_id FK→product_items(nullable),
  qty, unit_cost(decimal 12,2), total_cost(decimal 12,2)
```

### CUSTOMERS & ORDERS
```
customers
  id PK, name, phone, email, address, deleted_at, created_at

orders
  id PK, bill_no(unique), customer_id FK→customers(nullable),
  customer_name(varchar fallback), customer_phone(varchar fallback),
  store_id FK→stores, served_by FK→users,   ← sale happens at store/counter level
  gross_amount, discount_amount,
  service_charge_rate, service_charge,
  tax_rate, tax_amount,
  net_amount, paid_amount,
  payment_method ENUM(cash,card,mobile_money,credit),
  payment_status ENUM(unpaid,partial,paid),
  note, ordered_at(datetime), created_at, updated_at

order_items
  id PK, order_id FK→orders, product_id FK→products,
  variant_id FK→product_variants(nullable),
  product_item_id FK→product_items(nullable),
  qty, unit_price, discount, total
```

### RETURNS
```
returns
  id PK, return_no(unique), order_id FK→orders,
  store_id FK→stores, processed_by FK→users,   ← return processed at store level
  total_amount, reason(text), refund_method(varchar),
  status ENUM(pending,approved,rejected),
  note, created_at, updated_at

return_items
  id PK, return_id FK→returns, order_item_id FK→order_items,
  product_id FK→products, variant_id(nullable),
  product_item_id FK→product_items(nullable),
  qty, unit_price, total,
  condition ENUM(resalable,damaged,defective)
```

### NOTIFICATIONS
```
notifications
  id PK, user_id FK→users, title, message(text),
  type ENUM(info,warning,success,danger),
  link(varchar nullable), is_read(bool default 0), created_at
```

---

## MODELS — STATUS & WHAT TO DO

| Model file | Table | Status | Action needed |
|---|---|---|---|
| `BaseModel.php` | — | ✅ Done | None |
| `UserModel.php` | users | ✅ Done | Already has findForAuth, getPermissions, etc. |
| `RoleModel.php` | roles | ✅ Done | None |
| `PermissionModel.php` | permissions | ✅ Done | None |
| `LoginAttemptModel.php` | login_attempts | ✅ Done | None |
| `BranchModel.php` | branches | ❌ Deleted | **Create** — top-level physical location |
| `StoreModel.php` | stores | ❌ Deleted | **Create** — sub-division inside a branch |
| `CompanySettingsModel.php` | company_settings | ✅ Done | None |
| `CategoryModel.php` | categories | ✅ Done | None |
| `BrandModel.php` | brands | ✅ Done | None |
| `AttributeModel.php` | attributes | ✅ Done | None |
| `AttributeValueModel.php` | attribute_values | ✅ Done | None |
| `ProductModel.php` | products | ✅ Done | None |
| `ProductVariantModel.php` | product_variants | ✅ Done | None |
| `ProductItemModel.php` | product_items | ⚠️ Needs update | `branch_id` for received stock, `store_id` for counter stock |
| `StockLevelModel.php` | stock_levels | ⚠️ Needs update | `branch_id` (branch-held) OR `store_id` (counter-held) |
| `StockMovementModel.php` | stock_movements | ⚠️ Needs update | from_type/to_type values: 'branch'\|'store' |
| `SupplierModel.php` | suppliers | ✅ Done | None |
| `PurchaseModel.php` | purchases | ⚠️ Needs update | `branch_id FK→branches` (purchases received at branch) |
| `PurchaseItemModel.php` | purchase_items | ✅ Done | None |
| `CustomerModel.php` | customers | ✅ Done | None |
| `OrderModel.php` | orders | ⚠️ Needs update | `store_id FK→stores` (sales at counter level) |
| `OrderItemModel.php` | order_items | ✅ Done | None |
| `ReturnModel.php` | returns | ⚠️ Needs update | `store_id FK→stores` (returns at counter level) |
| `ReturnItemModel.php` | return_items | ✅ Done | None |
| `NotificationModel.php` | notifications | ✅ Done | None |

---

## CONTROLLERS — FULL LIST (all extend AppController unless stated)

### ✅ Already built (do not recreate)
| File | Extends | Purpose |
|---|---|---|
| `BaseController.php` | CI4 Controller | Abstract foundation — session, helpers, redirectIfAuthenticated() |
| `AppController.php` | BaseController | Abstract — loads user/permissions from session, render(), hasPermission(), deny() |
| `Auth.php` | BaseController | PUBLIC: login, authenticate, logout, forgotPassword, sendResetLink, resetPassword, doResetPassword |

### ❌ Must create — Core
| File | Extends | Key permissions | Methods |
|---|---|---|---|
| `Dashboard.php` | AppController | any logged-in | `index()` — stats scoped to user's branch/store; admin sees all |
| `Controller_Setting.php` | AppController | `settings.update` | `index()`, `update()` — company settings singleton |
| `Controller_Profile.php` | AppController | any logged-in | `index()`, `update()`, `changePassword()` — own profile only |

### ❌ Must create — Locations / Staff
| File | Extends | Key permissions | Methods |
|---|---|---|---|
| `Controller_Branch.php` | AppController | `branches.*` | `index()`, `fetchData()`, `create()`, `update($id)`, `delete($id)`, `assignStaff($id)` — top-level locations |
| `Controller_Store.php` | AppController | `stores.*` | `index()`, `fetchData()`, `create()`, `update($id)`, `delete($id)` — sub-divisions within a branch |
| `Controller_Staff.php` | AppController | `staff.*` | `index()`, `create()`, `edit($id)`, `delete($id)`, `profile($id)` — staff creation with branch + role |

### ❌ Must create — RBAC
| File | Extends | Key permissions | Methods |
|---|---|---|---|
| `Controller_Role.php` | AppController | `roles.*` (super admin only) | `index()`, `create()`, `edit($id)`, `delete($id)`, `syncPermissions($id)` |

### ❌ Must create — Catalogue
| File | Extends | Key permissions | Methods |
|---|---|---|---|
| `Controller_Category.php` | AppController | `categories.*` | `index()`, `create()`, `edit($id)`, `delete($id)` |
| `Controller_Brand.php` | AppController | `brands.*` | `index()`, `create()`, `edit($id)`, `delete($id)` |
| `Controller_Attribute.php` | AppController | `attributes.*` | `index()`, `create()`, `edit($id)`, `delete($id)`, `addValue()`, `deleteValue($id)` |
| `Controller_Supplier.php` | AppController | `suppliers.*` | `index()`, `create()`, `edit($id)`, `delete($id)` |

### ❌ Must create — Inventory
| File | Extends | Key permissions | Methods |
|---|---|---|---|
| `Controller_Product.php` | AppController | `products.*` | `index()`, `fetchData()`, `create()`, `edit($id)`, `delete($id)`, `variants($id)`, `stock($id)` |
| `Controller_Purchase.php` | AppController | `purchases.*` | `index()`, `create()`, `view($id)`, `delete($id)` |
| `Controller_StockAdjustment.php` | AppController | `stock.adjust` | `index()`, `adjust()` — manual qty correction |

### ❌ Must create — Sales
| File | Extends | Key permissions | Methods |
|---|---|---|---|
| `Controller_Order.php` | AppController | `orders.*` | `index()`, `fetchData()`, `create()` (POS), `view($id)`, `delete($id)`, `printReceipt($id)`, `downloadPDF($id)`, `getProductByIMEI()`, `getProductById()` |
| `Controller_Customer.php` | AppController | `customers.*` | `index()`, `create()`, `edit($id)`, `delete($id)`, `search()` (AJAX) |
| `Controller_Return.php` | AppController | `returns.*` | `index()`, `create()`, `view($id)`, `approve($id)`, `reject($id)` |

### ❌ Must create — Reporting & Notifications
| File | Extends | Key permissions | Methods |
|---|---|---|---|
| `Controller_Report.php` | AppController | `reports.view` | `index()`, `sales()`, `stock()`, `purchases()`, `staff()` — scoped by store for staff, branch for managers, all for admin |
| `Controller_Notification.php` | AppController | any logged-in | `poll()` (AJAX — unread count), `markRead()`, `markAllRead()` |

---

## VIEWS — FULL LIST

### Auth (public — no template wrapper)
| File | Status | Notes |
|---|---|---|
| `Views/login.php` | ✅ Exists | Add remember-me checkbox |
| `Views/auth/forgot_password.php` | ❌ Create | Email form |
| `Views/auth/reset_password.php` | ❌ Create | New password + confirm form |
| `Views/emails/password_reset.php` | ✅ Done | HTML email |

### Templates (shared layout)
| File | Status | Notes |
|---|---|---|
| `Views/templates/header.php` | ✅ Exists | Needs `current_user`, `permissions` vars |
| `Views/templates/header_menu.php` | ✅ Exists | Needs notification badge |
| `Views/templates/side_menubar.php` | ✅ Exists | Show/hide menu items by permission |
| `Views/templates/footer.php` | ✅ Exists | None |

### Dashboard
| File | Status | Notes |
|---|---|---|
| `Views/dashboard.php` | ✅ Exists (root) | Move to `Views/dashboard/index.php` |
| `Views/dasboard/admin.php` | ✅ Exists (typo) | Rename dir to `dashboard/` — admin stats view |
| `Views/dasboard/user.php` | ✅ Exists (typo) | Branch-scoped stats view |

### Settings & Profile
| File | Status | Notes |
|---|---|---|
| `Views/settings/index.php` | ❌ Create | Company settings form |
| `Views/profile/index.php` | ❌ Create | Own profile view/edit |
| `Views/profile/change_password.php` | ❌ Create | Password change form |

### Branches & Stores
| File | Status | Notes |
|---|---|---|
| `Views/branches/index.php` | ❌ Create | Top-level branch list + DataTable |
| `Views/branches/create.php` | ❌ Create | Name, address, phone, email |
| `Views/branches/edit.php` | ❌ Create | |
| `Views/branches/assign_staff.php` | ❌ Create | Multi-select staff for branch |
| `Views/stores/index.php` | ❌ Create | Store list filtered by branch |
| `Views/stores/create.php` | ❌ Create | Includes branch dropdown |
| `Views/stores/edit.php` | ❌ Create | |
| `Views/warehouse/index.php` | ✅ Exists | **Delete / replace** with branches/index.php |

### Staff & Roles
| File | Status | Notes |
|---|---|---|
| `Views/staff/index.php` | ❌ Create | Staff list |
| `Views/staff/create.php` | ❌ Create | Name, email, password, phone, gender, role dropdown, branch dropdown |
| `Views/staff/edit.php` | ❌ Create | Same fields, no password required |
| `Views/staff/profile.php` | ❌ Create | View a staff member's profile |
| `Views/members/` | ✅ Exists | **Delete** — replaced by `Views/staff/` |
| `Views/roles/index.php` | ❌ Create | Role list |
| `Views/roles/create.php` | ❌ Create | Role name + permission checkboxes grouped by module |
| `Views/roles/edit.php` | ❌ Create | |
| `Views/permission/` | ✅ Exists | **Delete** — replaced by `Views/roles/` |

### Catalogue
| File | Status | Notes |
|---|---|---|
| `Views/categories/index.php` | ❌ Create | Tree list |
| `Views/brands/index.php` | ❌ Create | |
| `Views/attributes/index.php` | ❌ Create | Attribute + inline value management |

### Products & Stock
| File | Status | Notes |
|---|---|---|
| `Views/products/index.php` | ✅ Exists | Needs update for new model |
| `Views/products/create.php` | ✅ Exists | Needs category/brand/variant fields |
| `Views/products/edit.php` | ✅ Exists | Needs update |
| `Views/products/stock.php` | ❌ Create | Stock levels per location |
| `Views/purchases/index.php` | ❌ Create | |
| `Views/purchases/create.php` | ❌ Create | Supplier + items |
| `Views/purchases/view.php` | ❌ Create | |
| `Views/suppliers/index.php` | ❌ Create | |

### Orders & Returns
| File | Status | Notes |
|---|---|---|
| `Views/orders/index.php` | ✅ Exists | Needs update for branch scope |
| `Views/orders/create.php` | ✅ Exists | POS — product search by IMEI or name |
| `Views/orders/edit.php` | ✅ Exists | Needs review |
| `Views/orders/receipt.php` | ❌ Create | Printable receipt |
| `Views/returns/index.php` | ❌ Create | |
| `Views/returns/create.php` | ❌ Create | Link to original order |
| `Views/returns/view.php` | ❌ Create | |

### Reports & Notifications
| File | Status | Notes |
|---|---|---|
| `Views/reports/index.php` | ✅ Exists | Needs rewrite for new schema |
| `Views/notifications/index.php` | ❌ Create | Full notification list |

### Customers
| File | Status | Notes |
|---|---|---|
| `Views/customers/index.php` | ❌ Create | |
| `Views/customers/create.php` | ❌ Create | |
| `Views/customers/edit.php` | ❌ Create | |

---

## PERMISSIONS TABLE — All permission names (module.action format)

| Module | Permissions |
|---|---|
| `stores` | stores.view, stores.create, stores.update, stores.delete |
| `branches` | branches.view, branches.create, branches.update, branches.delete, branches.assign_staff |
| `staff` | staff.view, staff.create, staff.update, staff.delete |
| `roles` | roles.view, roles.create, roles.update, roles.delete |
| `categories` | categories.view, categories.create, categories.update, categories.delete |
| `brands` | brands.view, brands.create, brands.update, brands.delete |
| `attributes` | attributes.view, attributes.create, attributes.update, attributes.delete |
| `suppliers` | suppliers.view, suppliers.create, suppliers.update, suppliers.delete |
| `products` | products.view, products.create, products.update, products.delete |
| `stock` | stock.view, stock.adjust |
| `purchases` | purchases.view, purchases.create, purchases.delete |
| `customers` | customers.view, customers.create, customers.update, customers.delete |
| `orders` | orders.view, orders.create, orders.update, orders.delete, orders.discount |
| `returns` | returns.view, returns.create, returns.approve |
| `reports` | reports.view |
| `settings` | settings.view, settings.update |

---

## MIGRATION — `2026-04-18-000001_CreateSchema.php`
- [ ] Single file, all tables in correct FK order (no forward refs)
- [ ] All tables use `utf8mb4` / `utf8mb4_unicode_ci`
- [ ] All `down()` methods drop tables in reverse order
- [ ] Proper indexes on all FK columns and search columns

## SEED — `MainSeeder.php`
- [ ] Super Admin role — all permissions
- [ ] Branch Manager role — branch/order/product/report permissions
- [ ] Staff role — orders.view, orders.create, products.view
- [ ] Admin user: id=1, email=admin@chloephones.com, password=Admin@1234
- [ ] Default branch: "Main Branch" (top-level location)
- [ ] Default store: "Main Store" under Main Branch
- [ ] Admin assigned Super Admin role + Main Branch
- [ ] Company settings row (id=1)

---

## ROUTES — `Config/Routes.php` (planned clean structure)

```
Public (no filter):
  GET/POST  auth/login
  GET       auth/logout
  GET/POST  auth/forgot-password
  GET       auth/reset-password/(:alphanum)
  POST      auth/reset-password

Protected (filter: auth):
  GET       dashboard

  GET/POST  branches, branches/create, branches/(:num)/edit, POST branches/(:num)/delete, branches/(:num)/staff
  GET/POST  stores, stores/create, stores/(:num)/edit, POST stores/(:num)/delete
  GET/POST  staff, staff/create, staff/(:num)/edit, staff/(:num)/delete

  GET/POST  roles, roles/create, roles/(:num)/edit, roles/(:num)/delete

  GET/POST  categories, brands, attributes, suppliers  (standard CRUD)

  GET/POST  products, products/create, products/(:num)/edit, products/(:num)/stock
  GET/POST  purchases, purchases/create, purchases/(:num)

  GET/POST  customers, orders (POS create), orders/(:num), orders/(:num)/receipt
  GET/POST  returns, returns/create, returns/(:num)

  GET       reports, reports/sales, reports/stock, reports/purchases

  GET       notifications/poll  (AJAX)
  POST      notifications/mark-read
  GET/POST  settings
  GET/POST  profile, profile/change-password
```

---

## EXECUTION ORDER
1. [ ] Create `BranchModel.php` (top-level) + `StoreModel.php` (sub-division)
2. [ ] Update `ProductItemModel`, `StockLevelModel`, `StockMovementModel`, `OrderModel`, `ReturnModel`, `PurchaseModel`
3. [ ] Write migration `2026-04-18-000001_CreateSchema.php`
4. [ ] Write `MainSeeder.php`
5. [ ] Run migration + seed: `php spark migrate --all` then `php spark db:seed MainSeeder`
6. [ ] Create all controllers (in order listed above)
7. [ ] Update `Config/Routes.php` to clean structure
8. [ ] Create/update all views

