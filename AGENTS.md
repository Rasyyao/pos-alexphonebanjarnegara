<claude-mem-context>
# Memory Context

# [alexphonebanjarnegara] recent context, 2026-06-21 6:55pm GMT+7

Legend: 🎯session 🔴bugfix 🟣feature 🔄refactor ✅change 🔵discovery ⚖️decision
Format: ID TIME TYPE TITLE
Fetch details: get_observations([IDs]) | Search: mem-search skill

Stats: 50 obs (17,525t read) | 553,676t work | 97% savings

### Jun 13, 2026
S25 Debounce Added to All Stock Filter Inputs in Livewire Component (Jun 13 at 11:10 PM)
### Jun 14, 2026
S26 Brand Management Modal Added to Units Index Page (Jun 14 at 11:19 AM)
S27 Capital Distribution Tracker Implemented in cashflow.blade.php (Jun 14 at 11:23 AM)
S28 Stock Add Bug — setVal Fallback Empty String Prevented Form Submission (Jun 14 at 11:42 AM)
### Jun 15, 2026
S30 Harga Modal Removal — Partial Completion Status Across Report Views (Jun 15 at 6:38 PM)
### Jun 20, 2026
S31 Decided to Remove Aset Bulan Lalu and Peningkatan Aset from Asset Excel Report (Jun 20 at 2:25 PM)
S32 Harga Beli (Purchase Price) Fully Removed from All UI Views (Jun 20 at 2:32 PM)
263 11:17p ⚖️ Harga Beli (Purchase Price) Field Removed from Sales Module
264 " ✅ Harga Beli Column Removed from Stock Filter Table (Partial)
265 11:18p ✅ Harga Beli (Purchase Price) Fully Removed from All UI Views
S33 Bug fix: Indonesian money input fields saving wrong values (100 juta stored as 1 juta) — root cause found and fixed in edit forms (Jun 20 at 11:18 PM)
266 11:23p 🔵 Currency Input Truncation Bug — 100 Juta Saved as 1 Juta
267 11:24p 🔵 Indonesian Number Formatting Handler in StoreUnitRequest — cleanMoney()
268 11:25p 🔵 Payment Method Splitting Logic in UnitController.resolveSplit()
269 " 🔵 Frontend Number Formatting Pattern in create.blade.php — calcCreateMargin()
270 11:26p 🔵 Database Schema for Units Table — purchase_price Field Definition
271 11:27p 🔵 Unit Model Casts purchase_price as 'decimal:2' — No Truncation at Model Layer
272 11:28p 🔵 UnitService.store() Passes Validated Data Directly to Repository
273 11:30p 🔴 Root Cause Found: Eloquent decimal:2 Cast Renders Decimal Point in Money Input Fields
S34 Accessories Verify Table Replaced "Harga Jual" Column with "Inputter" (Date + Time) (Jun 20 at 11:31 PM)
276 11:58p 🔵 Sales Verify View Missing Stock Input Display
### Jun 21, 2026
277 12:00a 🔵 SaleController.verify() Passes Unit and Accessory Data to View
279 12:02a 🔵 Units Verification Tab Missing Stock Quantity Display
280 12:04a 🔵 UnitService Auto-Approves Units Created by Superadmin, Bypassing Verification
281 12:07a 🔵 Unit Model Missing stock_qty Field Unlike Accessory Model
282 12:10a 🟣 Pending Stock Verification Banners Added to HP and Accessory Index Views
283 " 🟣 Sidebar Verify Badge Now Shows Combined Pending Count (Sales + Units + Accessories)
284 " 🟣 Role-Aware Success Messages Added to Unit and Accessory Store Actions
285 " 🟣 Accessories Verify Table Replaced "Harga Jual" Column with "Inputter" (Date + Time)
286 12:22a ⚖️ Role-Based Sales Verification Bypass for Superadmin
287 " 🟣 SaleService Updated to Support Role-Based Verification Bypass
288 12:23a 🟣 Superadmin Sales Auto-Approval Implemented in SaleService
S35 Superadmin Sales Auto-Approval Implemented in SaleService (Jun 21 at 12:23 AM)
289 12:32a ⚖️ Role-Based Daily Sales Report Edit Access Control — Tutup Buku Policy
290 12:33a 🔵 Existing Role Gates in Sales Views — Delete Already Superadmin-Only, Edit Not Date-Gated
360 6:05p 🟣 Estimasi Margin Hidden from Admin, Split Payment Edit Fix Requested
361 6:07p 🔵 Split Payment Architecture and Estimasi Margin Role-Gating — Pre-Edit Research
362 " 🟣 Estimasi Margin Hidden from Admin Role in Sales Show Page
363 " 🔴 Split Payment Fields Now Editable in Unit Edit Form
364 6:08p 🟣 Role-Based Estimasi Margin Visibility in Sales Show Page
365 " 🔴 Split Payment Edit Fixed in Units Edit Form
366 6:09p 🟣 Estimasi Margin Hidden from Admin Role in Sales Show View
367 " 🔴 Split Payment Edit Fixed in Unit Edit View
368 6:10p 🟣 Role-Based Estimasi Margin Visibility + Split Payment Edit Fix
369 " 🔵 UpdateUnitRequest::rules() Crashes with "Attempt to read property 'id' on string"
370 6:11p 🟣 Estimasi Margin Hidden from Admin, Visible to Superadmin in Sales Show Page
371 " 🔴 Split Payment Edit Fixed in units/edit.blade.php
372 6:12p 🟣 Estimasi Margin Hidden from Admin, Visible to Superadmin in Sales Show Page
373 " 🔴 Split Payment Fields Now Editable in Unit Edit Form
374 " 🟣 Estimasi Margin Hidden from Admin, Visible to Superadmin in Sales Show
375 " 🔴 Split Payment Edit Fields Now Editable in units/edit.blade.php
376 6:44p 🟣 Sales Show Page — Split Payment Detail Display & Edit Requested
377 " 🔵 Sales Module Architecture — Split Payment Schema & Edit Constraints Traced
378 6:45p 🟣 Sales Show Page — Split Payment Edit Support Requested
379 6:46p 🟣 Sales Show Page — Split Payment Display & Edit Feature Requested
380 6:48p 🔵 sales/show.blade.php — Split Selling Price Display and Edit Feature Request
381 6:49p 🟣 Sales Show Page — Split Payment Detail Display & Edit Request
382 " 🔵 SaleController::update() Crashes on null toDateString() — Line 84 Bug Found
383 6:50p 🟣 Sales Show Page — Split Payment Display & Edit Support Requested
384 6:51p 🔴 SaleController Guards Against Split Payment Changes on Partially-Paid Debts

Access 554k tokens of past work via get_observations([IDs]) or mem-search skill.
</claude-mem-context>