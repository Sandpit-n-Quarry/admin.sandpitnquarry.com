<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\TransporterController;
use App\Http\Controllers\ReloadController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\CoinPromotionController;
use App\Http\Controllers\CoinPromotionDetailController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CoinController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\TruckController;
use App\Http\Controllers\WheelController;
use App\Http\Controllers\BusinessPriceController;
use App\Http\Controllers\TransporterWithdrawalController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PriceItemController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\SiteController;


// Authentication
Route::controller(AuthenticationController::class)->prefix('authentication')->group(function () {
    Route::get('/signin', [AuthenticationController::class, 'signIn'])->name('signin');
    // Form POST for the signin page
    Route::post('/signin', 'postLogin')->name('signin.post');
    // Logout (kept here since it's related to authentication)
    Route::post('/logout', 'logout')->name('logout');
});

// Protected routes - Require authentication
Route::middleware(['auth'])->group(function () {
    // Dashboard root route
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/', 'index')->name('dashboard.index');
        Route::get('/analyst', 'analyst')->name('dashboard.Analyst');
    });

    // chart
    Route::prefix('chart')->group(function () {
        Route::controller(ChartController::class)->group(function () {
            Route::get('/columnchart', 'columnChart')->name('columnChart');
            Route::get('/linechart', 'lineChart')->name('lineChart');
            Route::get('/piechart', 'pieChart')->name('pieChart');
        });
    });

    // Prices and Zones
    Route::prefix('prices')->group(function () {
        Route::controller(PriceItemController::class)->group(function () {
            Route::get('/prices', 'index')->name('prices');
            Route::get('/prices/create', 'createPrice')->name('prices.create');
            Route::post('/prices', 'storePrice')->name('prices.store');
            Route::get('/{price}/edit', 'editPrice')->name('prices.edit');
            Route::put('/{price}', 'updatePrice')->name('prices.update');
            Route::get('/tonne/{priceId}', 'tonnePrices')->name('prices.tonne');
            Route::get('/load/{priceId}', 'loadPrices')->name('prices.load');
            Route::post('/tonne/update', 'updateTonnePrice')->name('prices.tonne.update');
            Route::post('/load/update', 'updateLoadPrice')->name('prices.load.update');
            Route::get('/prices/item', 'getPriceItem')->name('prices.item.get');
            Route::get('/zones', 'zones')->name('zones');
            Route::post('/zones/create', 'storeZone')->name('zones.create');
            Route::post('/zones/postcodes/update', 'updatePostcodes')->name('zones.postcodes.update');
            Route::post('/zones/postcodes/add', 'addPostcode')->name('zones.postcodes.add');
            Route::post('/zones/postcodes/remove', 'removePostcode')->name('zones.postcodes.remove');
            Route::delete('/{price}', 'destroy')->name('prices.destroy');
        });
    });

    // Users
    Route::prefix('users')->group(function () {
        Route::controller(UsersController::class)->group(function () {
            Route::get('/add-user', 'addUser')->name('addUser');
            Route::post('/store-user', 'storeUser')->name('storeUser');
            Route::get('/users-grid', 'usersGrid')->name('usersGrid');
            Route::get('/users-list', 'usersList')->name('usersList');
            Route::get('/view-profile/{id?}', 'viewProfile')->name('viewProfile');
            Route::get('/edit-user/{id}', 'editUser')->name('editUser');
            Route::put('/update-user/{id}', 'updateUser')->name('updateUser');
            Route::delete('/delete-user/{id}', 'deleteUser')->name('deleteUser');
        });
    });

    // Transporters
    Route::prefix('transporters')->group(function () {
        Route::controller(TransporterController::class)->group(function () {
            Route::get('/add-transporter', 'addTransporter')->name('addTransporter');
            Route::post('/store-transporter', 'storeTransporter')->name('storeTransporter');
            Route::get('/transporters-list', 'transportersList')->name('transportersList');
            Route::get('/view-transporter/{id}', 'viewTransporter')->name('viewTransporter');
            Route::get('/edit-transporter/{id}', 'editTransporter')->name('editTransporter');
            Route::put('/update-transporter/{id}', 'updateTransporter')->name('updateTransporter');
            Route::delete('/delete-transporter/{id}', 'deleteTransporter')->name('deleteTransporter');
        });
    });

    // Employees Routes
    Route::prefix('employees')->group(function () {
        Route::controller(EmployeesController::class)->group(function () {
            Route::get('/', 'index')->name('employees.index');
            Route::get('/create', 'create')->name('employees.create');
            Route::post('/', 'store')->name('employees.store');
            Route::get('/{id}', 'show')->name('employees.show');
            Route::get('/{id}/edit', 'edit')->name('employees.edit');
            Route::put('/{id}', 'update')->name('employees.update');
            Route::delete('/{id}', 'destroy')->name('employees.destroy');
        });
    });

    // Orders
    Route::prefix('orders')->group(function () {
        Route::controller(OrderController::class)->group(function () {
            Route::get('/orders-list', 'orders')->name('ordersList');
            Route::get('/order-details/{id}', 'orderDetails')->name('orderDetails');
            Route::get('/order-edit/{id}', 'orderEdit')->name('orderEdit');
            Route::get('/order-statuses', 'orderStatuses')->name('orderStatuses');
            Route::get('/free-deliveries', 'freeDeliveries')->name('freeDeliveries');
            Route::get('/self-pickups', 'selfPickups')->name('selfPickups');
        });
    });

    // Jobs
    Route::prefix('jobs')->group(function () {
        Route::controller(JobController::class)->group(function () {
            Route::get('/jobs-list', 'jobs')->name('jobsList');
            Route::get('/job-details/{id}', 'jobDetails')->name('jobDetails');
            Route::get('/job-statuses', 'jobStatuses')->name('jobStatuses');
        });
    });

    // Trips
    Route::prefix('trips')->group(function () {
        Route::controller(TripController::class)->group(function () {
            Route::get('/trips-list', 'trips')->name('tripsList');
            Route::get('/trip-details/{id}', 'tripDetails')->name('tripDetails');
            Route::get('/trip-statuses', 'tripStatuses')->name('tripStatuses');
        });
    });

    // Products
    Route::prefix('products')->group(function () {
        Route::controller(ProductController::class)->group(function () {
            Route::get('/', 'index')->name('products.index');
            Route::get('/create', 'create')->name('products.create');
            Route::post('/store', 'store')->name('products.store');
            Route::get('/{id}', 'show')->name('products.show');
            Route::get('/{id}/edit', 'edit')->name('products.edit');
            Route::put('/{id}', 'update')->name('products.update');
        });
    });

    // Sites/Quarries
    Route::prefix('sites')->group(function () {
        Route::controller(SiteController::class)->group(function () {
        Route::get('/', 'index')->name('sites.index');
        Route::get('/create', 'create')->name('sites.create');
        Route::post('/', 'store')->name('sites.store');
        Route::get('/{id}', 'show')->name('sites.show');
        Route::get('/{id}/edit', 'edit')->name('sites.edit');
        Route::put('/{id}', 'update')->name('sites.update');
        Route::delete('/{id}', 'destroy')->name('sites.destroy');
        });

    });

    // Accounts
    Route::prefix('accounts')->group(function () {
        Route::controller(AccountController::class)->group(function () {
            Route::get('/', 'index')->name('accounts.index');
            Route::get('/create', 'create')->name('accounts.create');
            Route::post('/', 'store')->name('accounts.store');
            Route::get('/{account}', 'show')->name('accounts.show');
            Route::get('/{account}/edit', 'edit')->name('accounts.edit');
            Route::put('/{account}', 'update')->name('accounts.update');
            Route::delete('/{account}', 'destroy')->name('accounts.destroy');
        });
    });

    // Customer Accounts
    Route::prefix('customer-accounts')->group(function () {
        Route::controller(CustomerAccountController::class)->group(function () {
            Route::get('/', 'index')->name('customer-accounts.index');
            Route::get('/create', 'create')->name('customer-accounts.create');
            Route::post('/', 'store')->name('customer-accounts.store');
            Route::get('/{customerAccount}', 'show')->name('customer-accounts.show');
            Route::get('/{customerAccount}/edit', 'edit')->name('customer-accounts.edit');
            Route::put('/{customerAccount}', 'update')->name('customer-accounts.update');
            Route::delete('/{customerAccount}', 'destroy')->name('customer-accounts.destroy');
            Route::get('/{customerAccount}/document', 'viewDocument')->name('customer-accounts.document');
        });
    });

    // Reloads
    Route::prefix('reloads')->group(function () {
        Route::controller(ReloadController::class)->group(function () {
            Route::get('/', 'index')->name('reloads.index');
            Route::get('/{reload}', 'show')->name('reloads.show');
        });
    });

    // Withdrawals
    Route::prefix('withdrawals')->group(function () {
        Route::controller(WithdrawalController::class)->group(function () {
            Route::get('/', 'index')->name('withdrawals.index');
            Route::get('/{withdrawal}/edit', 'edit')->name('withdrawals.edit');
            Route::post('/{withdrawal}', 'update')->name('withdrawals.update');
            Route::get('/{withdrawal}/bank-statement', 'viewBankStatement')->name('withdrawals.bank-statement');
        });
    });

    // Coins
    Route::prefix('coins')->group(function () {
        Route::controller(CoinController::class)->group(function () {
            Route::get('/', 'index')->name('coins.index');
            Route::get('/create', 'create')->name('coins.create');
            Route::post('/', 'store')->name('coins.store');
            Route::get('/{coin}/edit', 'edit')->name('coins.edit');
            Route::put('/{coin}', 'update')->name('coins.update');
            Route::delete('/{coin}', 'destroy')->name('coins.destroy');
        });
    });

    // Coin Promotions
    Route::prefix('coin-promotions')->group(function () {
        Route::controller(CoinPromotionController::class)->group(function () {
            Route::get('/', 'index')->name('coin-promotions.index');
            Route::get('/create', 'create')->name('coin-promotions.create');
            Route::post('/', 'store')->name('coin-promotions.store');
            Route::get('/{coinPromotion}/edit', 'edit')->name('coin-promotions.edit');
            Route::put('/{coinPromotion}', 'update')->name('coin-promotions.update');
            Route::delete('/{coinPromotion}', 'destroy')->name('coin-promotions.destroy');
        });
    });

    // Coin Promotion Details
    Route::prefix('coin-promotion-details')->group(function () {
        Route::controller(CoinPromotionDetailController::class)->group(function () {
            Route::get('/create', 'create')->name('coin-promotion-details.create');
            Route::post('/', 'store')->name('coin-promotion-details.store');
            Route::get('/{coinPromotionDetail}/edit', 'edit')->name('coin-promotion-details.edit');
            Route::put('/{coinPromotionDetail}', 'update')->name('coin-promotion-details.update');
            Route::delete('/{coinPromotionDetail}', 'destroy')->name('coin-promotion-details.destroy');
        });
    });

    // Assignments
    Route::prefix('assignments')->group(function () {
        Route::controller(AssignmentController::class)->group(function () {
            Route::get('/', 'index')->name('assignments.index');
            Route::get('/create', 'create')->name('assignments.create');
            Route::post('/', 'store')->name('assignments.store');
            Route::get('/{assignment}', 'show')->name('assignments.show');
            Route::get('/{assignment}/edit', 'edit')->name('assignments.edit');
            Route::put('/{assignment}', 'update')->name('assignments.update');
            Route::delete('/{assignment}', 'destroy')->name('assignments.destroy');
        });
    });

    // Payments
    Route::prefix('payments')->group(function () {
        Route::controller(PaymentController::class)->group(function () {
            Route::get('/', 'index')->name('payments.index');
            Route::get('/create', 'create')->name('payments.create');
            Route::post('/', 'store')->name('payments.store');
            Route::get('/{payment}', 'show')->name('payments.show');
            Route::get('/{payment}/edit', 'edit')->name('payments.edit');
            Route::put('/{payment}', 'update')->name('payments.update');
            Route::delete('/{payment}', 'destroy')->name('payments.destroy');
            Route::post('/{payment}/approve', 'approve')->name('payments.approve');
            Route::post('/{payment}/reject', 'reject')->name('payments.reject');
        });
    });

    // Drivers
    Route::prefix('drivers')->group(function () {
        Route::controller(DriverController::class)->group(function () {
            Route::get('/', 'index')->name('drivers.index');
            Route::get('/create', 'create')->name('drivers.create');
            Route::post('/', 'store')->name('drivers.store');
            Route::get('/{driver}', 'show')->name('drivers.show');
            Route::get('/{driver}/edit', 'edit')->name('drivers.edit');
            Route::put('/{driver}', 'update')->name('drivers.update');
            Route::delete('/{driver}', 'destroy')->name('drivers.destroy');
        });
    });

    // Trucks
    Route::prefix('trucks')->group(function () {
        Route::controller(TruckController::class)->group(function () {
            Route::get('/', 'index')->name('trucks.index');
            Route::get('/create', 'create')->name('trucks.create');
            Route::post('/', 'store')->name('trucks.store');
            Route::get('/{truck}', 'show')->name('trucks.show');
            Route::get('/{truck}/edit', 'edit')->name('trucks.edit');
            Route::put('/{truck}', 'update')->name('trucks.update');
            Route::delete('/{truck}', 'destroy')->name('trucks.destroy');
        });
    });
    // Business Prices
    Route::prefix('business-prices')->group(function () {
        Route::controller(BusinessPriceController::class)->group(function () {
        Route::get('/', 'index')->name('business-prices.index');
        Route::get('/{id}/edit-status', 'editStatus')->name('business-prices.edit-status');
        Route::post('/{id}/update-status', 'updateStatus')->name('business-prices.update-status');
        });
    });
    
    //Transporter Withdrawals
    Route::prefix('transporter-withdrawals')->group(function () {
        Route::controller(TransporterWithdrawalController::class)->group(function () {
            Route::get('/', 'index')->name('transporter-withdrawals.index');
            Route::post('/{id}/update-status', 'updateStatus')->name('transporter-withdrawals.update-status');
        });
    });
    
    // Wheels
    Route::prefix('wheels')->group(function () {
        Route::controller(WheelController::class)->group(function () {
            Route::get('/', 'index')->name('wheels.index');
            Route::get('/create', 'create')->name('wheels.create');
            Route::post('/', 'store')->name('wheels.store');
            Route::get('/{wheel}', 'show')->name('wheels.show');
            Route::get('/{wheel}/edit', 'edit')->name('wheels.edit');
            Route::put('/{wheel}', 'update')->name('wheels.update');
            Route::delete('/{wheel}', 'destroy')->name('wheels.destroy');
            // API route for toggling properties
            Route::post('/toggle-property', 'toggleProperty')->name('wheels.toggle-property');
            Route::get('/api/get-wheels', 'getWheels')->name('wheels.get_wheels');
        });
    });

        // Packages
        Route::prefix('packages')->group(function () {
            Route::controller(PackageController::class)->group(function () {
                Route::get('/', 'index')->name('packages.index');
                Route::get('/create', 'create')->name('packages.create');
                Route::post('/', 'store')->name('packages.store');
                Route::get('/{package}', 'show')->name('packages.show');
                Route::get('/{package}/edit', 'edit')->name('packages.edit');
                Route::put('/{package}', 'update')->name('packages.update');
                Route::delete('/{package}', 'destroy')->name('packages.destroy');
            });
        });
        // Permissions
         Route::prefix('permissions')->group(function () {
            Route::controller(PermissionController::class)->group(function () {
                Route::get('/', 'index')->name('permissions.index');
                Route::post('/assign', 'assign')->name('permissions.assign');
            });
        });
});


