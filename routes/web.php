<?php
use App\Http\Controllers\Dashboard\Article\ArticlesController;
use App\Http\Controllers\Dashboard\Communication\EmailController;
use App\Http\Controllers\Dashboard\Communication\ScheduleController;
use App\Http\Controllers\Dashboard\EventsAndSeminars\EventsController;
use App\Http\Controllers\Dashboard\EventsAndSeminars\NoticesController;
use App\Http\Controllers\Dashboard\EventsAndSeminars\SeminarController;
use App\Http\Controllers\Dashboard\Finance\FinancialController;
use App\Http\Controllers\Dashboard\HomeController;
use App\Http\Controllers\Dashboard\Notifications\NotificationsController;
use App\Http\Controllers\Dashboard\PaymentSettings\PaymentSettingsController;
use App\Http\Controllers\Dashboard\People\PeopleController;
use App\Http\Controllers\Dashboard\Profile\ProfileController;
use App\Http\Controllers\Dashboard\Search\SearchController;
use App\Http\Controllers\Dashboard\Shop\ShopController;
use App\Http\Controllers\Dashboard\SMS\SMSController;
use App\Http\Controllers\Dashboard\Spiritual\PrayersController;
use App\Http\Controllers\Dashboard\Spiritual\SermonController;
use App\Http\Controllers\Dashboard\Settings\CurrencySettingsController;
use App\Http\Controllers\Dashboard\Settings\GameLevelSettingsController;
use App\Http\Controllers\Dashboard\Settings\GeneralSettingsController;
use App\Http\Controllers\Dashboard\Settings\NotificationSettingsController;
use App\Http\Controllers\Dashboard\Settings\TopupAccountSettingsController;
use App\Http\Controllers\Dashboard\Spiritual\TestimonialController;
use App\Http\Controllers\Dashboard\Users\AttendanceController;
use App\Http\Controllers\Dashboard\People\PastorsController;
use App\Http\Controllers\Dashboard\Users\RolesController;
use App\Http\Controllers\Dashboard\Users\UsersController;
use App\Http\Controllers\Dashboard\Websites\GalleryController;
use App\Http\Controllers\Dashboard\Websites\HomePageSettingsController;
use App\Http\Controllers\Dashboard\Websites\OrderOfServiceSettingsController;
use App\Http\Controllers\Dashboard\Websites\PastorSettingsController;
use App\Http\Controllers\Dashboard\Websites\WebsiteSettingsController;
use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [IndexController::class, 'index']);
Route::get('/people', [IndexController::class, 'people']);
Route::get('/noticeboard', [IndexController::class, 'notices']);
Route::get('/events_seminars', [IndexController::class, 'events']);
Route::get('/events/view/{id}', [IndexController::class, 'event']);
Route::get('/seminar/view/{id}', [IndexController::class, 'seminar']);
Route::get('/spiritual', [IndexController::class, 'spiritual']);
Route::get('/our_gallery', [IndexController::class, 'gallery']);
Route::get('/our_articles', [IndexController::class, 'articles']);
Route::get('/shop', [IndexController::class, 'shop']);
Route::get('communities/{id}', [IndexController::class, "community"]);
Route::get('articles/{id}', [IndexController::class, "article"]);
Route::get('sermons/{id}', [IndexController::class, "sermon"]);
Route::get('notices/view/{id}', [IndexController::class, "notice"]);
Route::get('departments/view/{id}', [IndexController::class, "department"]);
Route::get('prayers/view/{id}', [IndexController::class, "prayer"]);
Route::get('registration', [IndexController::class, "registration"]);
Route::post('registration', [IndexController::class, "saveregistration"]);
Route::get('/check-login', [IndexController::class, 'checkLogin']);
/*Route::get('/get/genders',[IndexController::class, 'getGenders']);
Route::get('see/mail', function(){
    return view('mails.mail');
});*/

Auth::routes(/*['verify' => true]*/);

Route::get('home', function () {
    return redirect()->to('dashboard/home');
});
Route::group(['prefix' => 'dashboard', 'middleware' => ['auth']], function () {
    //home
    Route::get('home', [HomeController::class, 'index'])->name('home');
    //website settings
    Route::get('website/settings', [WebsiteSettingsController::class, 'index']);
    Route::post('website/settings/add', [WebsiteSettingsController::class, 'addSettings']);
    Route::post('website/settings/uploadfavicon', [WebsiteSettingsController::class, 'uploadfavicon']);
    Route::post('website/settings/uploadicon', [WebsiteSettingsController::class, 'uploadicon']);

    //homepage settings
    Route::get('website/homepage', [HomePageSettingsController::class, 'index']);
    Route::post('website/homepage/upload', [HomePageSettingsController::class, 'uploadHomePage']);
    Route::post('website/homepage/add', [HomePageSettingsController::class, 'addHomePage']);

    //Gallery Settings
    Route::get('website/gallery', [GalleryController::class, 'index']);
    Route::get('website/gallery/categories', [GalleryController::class, 'categories']);
    Route::post('website/gallery/categories/add', [GalleryController::class, 'addcategory']);
    Route::post('website/gallery/upload', [GalleryController::class, 'uploadgallery']);
    Route::get('/website/gallery/category/{id}', [GalleryController::class, 'viewcategory']);
    Route::get('website/gallery/category/delete/{id}', [GalleryController::class, 'deletecategory']);
    Route::get('website/gallery/delete/{id}', [GalleryController::class, 'deletegallery']);
    //Pastors Settings
    Route::get('website/pastorsmessage', [PastorSettingsController::class, 'getMessage']);
    Route::post('website/pastorsmessage/image/upload', [PastorSettingsController::class, 'uploadimage']);
    Route::post('website/pastorsmessage/add', [PastorSettingsController::class, 'addPastorMessage']);
    //
    /*
    Route::get('/communities', 'CommunitiesController::class, 'index']);
    Route::get('/newcommunity', 'CommunitiesController@newcommunity');
    Route::get('/editcommunity/{id}', 'CommunitiesController@editcommunity');
    Route::post('/addcommunity', 'CommunitiesController@addcommunity');
    Route::get('/removecommunity/{id}', 'CommunitiesController@removecommunity');*/
    Route::get('website/orderofservice', [OrderOfServiceSettingsController::class, 'orderofservice']);
    Route::post('website/service/add', [OrderOfServiceSettingsController::class, 'addservice']);
    Route::get('website/service/remove/{id}', [OrderOfServiceSettingsController::class, 'removeservice']);

    Route::get('website/weeklyverse', [OrderOfServiceSettingsController::class, 'weeklyverse']);
    Route::post('website/weeklyverse/add', [OrderOfServiceSettingsController::class, 'addverse']);

    //Financial Controller
    Route::get('finances/overview', [FinancialController::class, 'overview']);

    Route::get('finances/funds', [FinancialController::class, 'index']);
    Route::get('finances/datatables/funds', [FinancialController::class, 'datatablefunds']);
    Route::get('/funds/search', [FinancialController::class, 'fundssearch']);

    Route::get("finances/tithing/individual", [FinancialController::class, "individualtithing"]);

    Route::get('finances/expenses', [FinancialController::class, 'expenses']);
    Route::get('finances/datatables/expenses', [FinancialController::class, 'datatableexpenses']);


    Route::post('finances/funds/save', [FinancialController::class, 'savefunds']);
    Route::get('finances/funds/remove/{id}', [FinancialController::class, 'removefund']);

    //Payments Settings
    Route::get('/settings/funds/sources', [FinancialController::class, 'fundsource']);
    Route::post('/settings/funds/sources/save', [FinancialController::class, 'savefundsource']);
    Route::get('settings/funds/sources/remove/{id}', [FinancialController::class, 'removefundsource']);
    Route::get('settings/funds/mode/remove/{id}', [FinancialController::class, 'removefundmode']);
    Route::post('settings/funds/mode/save', [FinancialController::class, 'saveModeOfPayment']);
    Route::get('settings/ajax/sources/{id}', [FinancialController::class, 'getsources']);
    Route::get('settings/ajax/payment/{id}', [FinancialController::class, 'getpayments']);

    Route::get('finances/donations', [FinancialController::class, 'donations']);
    Route::get('finances/assets', [FinancialController::class, 'assets']);
    Route::post('finances/assets/add', [FinancialController::class, 'saveassets']);
    Route::get('finances/assets/remove/{id}', [FinancialController::class, 'removeasset']);

    //missing phones
    Route::get('finances/missing_mpesa_phones', [FinancialController::class, 'missingMpesaPhones']);
    Route::get('finances/datatable/missing_mpesa_phones', [FinancialController::class, 'getMissingMpesaPhones']);
    Route::post('finances/missing_mpesa_phones/add', [FinancialController::class, 'addMissingMpesaPhone']);

    //Activities and Pledges
    Route::get('finances/activities', [FinancialController::class, 'activities']);
    Route::get("finances/activities/remove/{id}", [FinancialController::class, "removeactivity"]);
    Route::post('finances/activities/add', [FinancialController::class, 'addactivity']);
    Route::get('/getactivity/{id}', [FinancialController::class, 'getactivity']);
    Route::get('finances/activities/pledges/{id}', [FinancialController::class, 'pledges']);
    Route::post("finances/activities/users/groups/add", [FinancialController::class, "addgroup"]);
    Route::get('ajax/pledge/{id}', [FinancialController::class, 'getPledges']);

    Route::post('finances/activities/pledges/sms', [SMSController::class, 'pledges']); //add pledge and send sms
    Route::post('pledge/edit', [SMSController::class, 'editPledge']);//edit pledge and send sms
    Route::post('finances/activities/pledge/remind', [SMSController::class, 'pledgeReminder']);
    Route::get('pledges/remove/{id}', [FinancialController::class, 'removepledge']);
    Route::post('pledge/pay', [SMSController::class, 'paypledge']);

    Route::get('finances/ajax/pledges/users', [FinancialController::class, 'getUsers']);
    Route::get('finances/activities/pledges/groups/{id}', [FinancialController::class, 'groups']);
    Route::get('ajax/groups/users', [FinancialController::class, 'getAjaxUsers']);
    Route::get('/pledges/recieved/{id}', [FinancialController::class, 'recieved']);
    Route::get('/groups/recieved/{id}', [FinancialController::class, 'recievedgroups']);
    Route::get('/groups/remove/{id}', [FinancialController::class, 'removegroup']);
    Route::get('finances/activities/groups/participants/{id}', [FinancialController::class, 'groupsparticipants']);
    Route::post('finances/activities/groups/pledges/members/add', [SMSController::class, 'addpledgegroupmembers']);

    //temp pdf
    Route::get("finances/printpdf/budget/{id}", [FinancialController::class, 'budgetpdf']);

    //Budget
    Route::get("finances/budgets", [FinancialController::class, "budgets"]);
    Route::post("finances/budget/save", [FinancialController::class, "addbudget"]);
    Route::get("finances/budgets/remove/{id}", [FinancialController::class, "removebudget"]);
    Route::get("finances/budgets/edit/{id}", [FinancialController::class, "budget"]);
    Route::get("finances/budgets/preview/{id}", [FinancialController::class, "previewbudget"]);

    //Individual Tithing
    Route::get("finances/users/search/{search}", [FinancialController::class, "search"]);
    Route::post("finances/tithing/individual/save", [FinancialController::class, "saveindividualtithe"]);
    Route::get("finances/tithing/{id}", [FinancialController::class, "tithes"]);

    //funds summaries
    Route::get("finances/summaries", [FinancialController::class, "summaries"]);
    Route::post("finances/summaries/settings/add", [FinancialController::class, "summaries_settings"]);

    //sermons
    Route::get('spiritual/sermons', [SermonController::class, 'index']);
    Route::get('spiritual/sermons/new', [SermonController::class, 'sermon']);
    Route::post('spiritual/sermons/add', [SermonController::class, 'addsermon']);
    Route::get('spiritual/sermons/edit/{id}', [SermonController::class, 'editsermon']);
    Route::post('spiritual/sermons/edit', [SermonController::class, 'editmysermon']);
    Route::get('spiritual/sermons/delete/{id}', [SermonController::class, 'deletesermon']);

    //Testimonials
    Route::get('spiritual/testimonials', [TestimonialController::class, 'index'])->name('testimonials');
    Route::post('spiritual/testimonials/add', [TestimonialController::class, 'save']);
    Route::get('spiritual/testimonials/activate/{id}', 'TestimonialController@activate');
    Route::get('spiritual/testimonials/deactivate/{id}', 'TestimonialController@deactivate');
    Route::get('spiritual/testimonials/{id}', "TestimonialController@testimonial");

    //events
    Route::get('events_and_notices/events', [EventsController::class, 'index']);
    Route::post('events_and_notices/events/add', [EventsController::class, 'addevent']);
    Route::get('events_and_notices/events/{id}', [EventsController::class, 'getevent']);
    Route::get('events_and_seminars/events/delete/{id}', [EventsController::class, 'removeEvent']);

    //notices
    Route::get('events_and_notices/notices', [NoticesController::class, 'index']);
    Route::post('events_and_notices/notices/add', [NoticesController::class, 'addnotice']);
    Route::get('events_and_notices/notices/{id}', [NoticesController::class, 'getnotice']);
    Route::get('events_and_notices/notices/delete/{id}', [NoticesController::class, 'deletenotice']);
    /*
    Route::get('/departments', 'DepartmentsController@index');
    Route::post('/adddepartment', 'DepartmentsController@adddepartment');
    Route::get('/departments/{id}', 'DepartmentsController@getdepartment');
    Route::get('/deletedepartment/{id}', 'DepartmentsController@deletedepartment');*/
    //seminors
    Route::get("events_and_notices/seminars", [SeminarController::class, "index"]);
    Route::post("events_and_notices/seminars/add", [SeminarController::class, "addseminar"]);
    Route::get("events_and_notices/seminars/{id}", [SeminarController::class, "getSeminar"]);
    //attendance
    Route::get("events_and_notices/attendance", [AttendanceController::class, "index"]);


    //people
    Route::get('people/new', [PeopleController::class, 'newpeople']);
    Route::get('people/users', [PeopleController::class, 'users']);
    Route::get('people/edit/{category}/{id}', [PeopleController::class, 'newpeople']);
    Route::get('people/members/{id}', [PeopleController::class, 'members']);
    Route::post('people/members/add', [PeopleController::class, 'addmembers']);
    Route::post('people/add', [PeopleController::class, 'addpeoplegroups']);
    Route::get('/people/communities', [PeopleController::class, 'people']);
    Route::get('/people/departments', [PeopleController::class, 'people']);
    Route::get('/people/member/activate/{id}', [PeopleController::class, 'activate']);
    Route::get('/people/member/deactivate/{id}', [PeopleController::class, 'deactivate']);
    Route::get('/people/member/remove/{id}', [PeopleController::class, 'remove']);

    //prayers
    Route::get('/spiritual/prayers', [PrayersController::class, 'index']);
    Route::get('spiritual/prayers/{id}', [PrayersController::class, 'getprayer']);
    Route::post('spiritual/prayers/add', [PrayersController::class, 'addprayer']);
    Route::get('spiritual/prayers/delete/{id}', [PrayersController::class, 'deleteprayer']);

    //shop
    Route::get("shop/products", [ShopController::class, "products"]);
    Route::get("shop/products/{id}", [ShopController::class, "product"]);
    Route::post("shop/products/add", [ShopController::class, "addproduct"]);
    Route::post("shop/products/save", [ShopController::class, "saveproduct"]);
    Route::post("shop/products/image/edit", [ShopController::class, 'editproductimage']);
    Route::post("shop/products/edit", [ShopController::class, "editproduct"]);
    Route::get("shop/products/remove/{id}", [ShopController::class, "removeproduct"]);

    Route::get("shop/purchases", [ShopController::class, "purchases"]);

    //update contacts
    Route::get("user/contacts/update", "UsersController@updateMyContacts");

    //Attendance
    Route::get("attendance", [AttendanceController::class, "index"]);
    Route::get('children', [AttendanceController::class, 'children']);
    Route::get('children/view/{id}', [AttendanceController::class, 'child']);
    Route::get('children/events/search', [AttendanceController::class, 'searchChildrenEvents']);
    Route::get('children/parents/search', [AttendanceController::class, 'searchChildrenParents']);
    Route::get('children/datatables/guardians/{id}', [AttendanceController::class, 'getChildGuardians']);
    Route::post('children/parent/add', [AttendanceController::class, 'addParent']);
    Route::get('datatables/children', [AttendanceController::class, 'getChildren']);
    Route::post('children/upload', [AttendanceController::class, 'importChildren']);
    Route::get("children/attendance", [AttendanceController::class, "childrenAttendance"]);
    Route::get("children/datatable/events", [AttendanceController::class, "getChildrenEvents"]);
    Route::post('children/save/checkin', [AttendanceController::class, 'saveChildrenCheckin']);
    Route::get("children/attendance/{id}", [AttendanceController::class, "showChildrenAttendance"]);
    Route::post('children/save/attendance', [AttendanceController::class, 'saveChildrenAttendance']);
    Route::post('children/save', [AttendanceController::class, 'saveChild']);
    Route::get('children/datatable/attendance/{id}', [AttendanceController::class, 'getChildrenAttendance']);
    Route::get('children/checkout/{id}', [AttendanceController::class, 'checkOut']);

    Route::get("ajax/attendance/{group}/{time}", [AttendanceController::class, "ajaxAttendance"]);
    Route::get("ajax/events", [AttendanceController::class, "ajaxEvents"]);
    Route::get("ajax/seminars", [AttendanceController::class, "ajaxSeminars"]);
    Route::get("ajax/services", [AttendanceController::class, "ajaxServices"]);
    Route::get("datatables/attendance", [AttendanceController::class, "datatablesAttendance"]);
    Route::get("events_and_notices/attendance/groups", [AttendanceController::class, "attendancegroups"]);
    Route::post("events_and_notices/attendance/group/add", [AttendanceController::class, "addAttendanceGroup"]);
    Route::post("events_and_notices/attendance/add", [AttendanceController::class, "addAttendance"]);
    Route::get("events_and_notices/attendance/new", [AttendanceController::class, "newAttendance"]);
    Route::get("events_and_notices/attendance/remove/{id}", [AttendanceController::class, "removeAttendance"]);
    Route::get("events_and_notices/attendance/groups/remove/{id}", [AttendanceController::class, "removeAttendanceGroup"]);

    //pastors
    Route::get('people/pastors', [PastorsController::class, 'pastors']);
    Route::post('/addpastor', [PastorsController::class, 'addpastor']);
    Route::get('/remove/pastor/{id}', [PastorsController::class, 'removepastor']);
    Route::get('/pastor/senior/{id}', [PastorsController::class, 'seniorpastor']);

    //articles
    Route::get('/articles', [ArticlesController::class, 'index']);
    Route::get('/articles/new', [ArticlesController::class, 'newarticle']);
    Route::post('/articles/add', [ArticlesController::class, 'addarticle']);
    Route::get('/articles/activate/{id}', [ArticlesController::class, 'activate']);
    Route::get('/articles/deactivate/{id}', [ArticlesController::class, 'deactivate']);
    Route::get('/articles/remove/{id}', [ArticlesController::class, 'removearticle']);

    /*Route::get('/profile', 'UsersController@profile');
    Route::post('/updateprofile', 'UsersController@updateprofile');
    Route::post('/profileimage', 'UsersController@profileimage');
    Route::post('/contacts', 'UsersController@addcontacts');*/

    //Notifications
    Route::get('notifications', [NotificationsController::class, 'index']);
    Route::get('datatable/notifications', [NotificationsController::class, 'getNotifications']);
    Route::get('notifications/view/{id}', [NotificationsController::class, 'notification']);


    //emails
    /*Route::get('emails', [EmailController::class, 'index']);
    Route::get('datatable/emails', [EmailController::class, 'getEmails']);
    Route::get('emails/send', [EmailController::class, 'email']);
    Route::post('emails/send', [EmailController::class, 'sendMail']);
    Route::get('emails/view/{id}', [EmailController::class, 'viewEmail']);*/

    //communication
    Route::get("communication/emails", [EmailController::class, "index"]);
    Route::get("communication/emails/users", [EMailController::class, "getemails"]);
    Route::get("communication/emails/view/{id}", [EmailController::class, "email"]);
    Route::get('communication/emails/delete/{id}', [EmailController::class, 'removeemail']);
    Route::post('communication/emails/send', [EMailController::class, 'html_email']);

    //sms
    Route::get("communication/sms", [SMSController::class, "sms"]);
    Route::post('communication/sms/send', [SMSController::class, 'sendSms']);
    Route::get('communication/sms/phone_numbers', [SMSController::class, "phoneNumbers"]);
    Route::get('communication/sms/phone_numbers/{search}', [SMSController::class, "phoneNumbers"]);
    Route::get('communication/sms/view/{id}', [SMSController::class, 'readsms']);
    Route::get('communication/sms/remove/{id}', [SMSController::class, 'removesms']);

    //scheduling
    Route::get("communication/schedule/sms", [ScheduleController::class, "schedules"]);
    Route::get("communication/schedule/sms/{id}", [ScheduleController::class, "schedule"]);
    Route::get("communication/schedule/sms/cancel/{id}", [ScheduleController::class, "cancelschedule"]);
    /*
    Route::get('user/phone/{id}', 'SMSController@getPhone');
    Route::post('users/sendsms', 'SMSController@sendsinglesms');
    Route::get('smsexample', function () {
        return view('admin.example');
    });*/

    //users
    Route::get('users/all', [UsersController::class, 'index']);
    Route::get('users/datatable', [UsersController::class, 'getUsers']);
    Route::post('users/add', [UsersController::class, 'addUser']);
    Route::get('users/view/{id}', [UsersController::class, 'viewUser']);
    Route::post('users/share/add', [UsersController::class, 'addShare']);
    Route::get('users/view/datatable/shares/{id}', [UsersController::class, 'getShares']);
    Route::get('users/roles', [RolesController::class, 'index']);
    Route::get('users/datatable/roles', [RolesController::class, 'getRoles']);
    Route::post('users/roles/add', [RolesController::class, 'addRole']);
    Route::get('users/roles/view/{id}', [RolesController::class, 'viewRole']);
    Route::post('users/roles/permissions/add', [RolesController::class, 'addPermissions']);
    //payment settings
    Route::get('payments/settings/banks', [PaymentSettingsController::class, 'index']);
    Route::get('payments/settings/datatable/banks', [PaymentSettingsController::class, 'getBanks']);
    Route::post('payments/settings/banks/add', [PaymentSettingsController::class, 'addBank']);

    Route::get('payments/settings/upi', [PaymentSettingsController::class, 'upi']);
    Route::get('payments/settings/datatable/upis', [PaymentSettingsController::class, 'getUPIs']);
    Route::post('payments/settings/upis/add', [PaymentSettingsController::class, 'addUPI']);

    //settings
    Route::get('settings/game_levels', [GameLevelSettingsController::class, 'index']);
    Route::get('settings/datatable/game_levels', [GameLevelSettingsController::class, 'getGameLevels']);
    Route::post('settings/game_levels/add', [GameLevelSettingsController::class, 'addGameLevel']);

    Route::get('settings/topup_accounts', [TopupAccountSettingsController::class, 'index']);
    Route::get('settings/datatable/topup_accounts', [TopupAccountSettingsController::class, 'getTopupAccounts']);
    Route::post('settings/topup_accounts/add', [TopupAccountSettingsController::class, 'addTopupAccount']);

    Route::get('settings/currencies', [CurrencySettingsController::class, 'index']);
    Route::get('settings/datatable/currencies', [CurrencySettingsController::class, 'getCurrencies']);
    Route::post('settings/currencies/add', [CurrencySettingsController::class, 'addCurrency']);

    Route::get('settings/notifications', [NotificationSettingsController::class, 'index']);
    Route::get('settings/datatable/notifications', [NotificationSettingsController::class, 'getNotifications']);
    Route::post('settings/notifications/add', [NotificationSettingsController::class, 'addNotification']);

    Route::get('settings/general', [GeneralSettingsController::class, 'index']);
    Route::post('settings/general/add', [GeneralSettingsController::class, 'addGeneralSettings']);

    //profile
    Route::get('profile', [ProfileController::class, 'index']);
    Route::post('profile/change', [ProfileController::class, 'editProfile']);
    Route::post('profile/change/password', [ProfileController::class, 'changePassword']);
    Route::post('profile/upload/picture', [ProfileController::class, 'uploadProfilePicture']);

    //Search
    Route::get('search/timezones', [SearchController::class, 'searchTimezones']);
    Route::get('search/maturities', [SearchController::class, 'searchMaturities']);
    Route::get('search/users', [SearchController::class, 'searchUsers']);
    Route::get('search/roles', [SearchController::class, 'searchRoles']);
    Route::get('search/days', [SearchController::class, 'searchDays']);
    Route::get('search/payments/{payment_method}/{user_id}', [SearchController::class, 'searchPaymentMethods']);
});
