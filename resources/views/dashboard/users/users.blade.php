@extends('layouts.dashboard')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h5 class="m-0 text-header"><i class='fas fa-users'></i> Users</h5>
                </div>
                <div class="col-sm-6 d-none d-sm-block">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('dashboard/home') }}">Home</a></li>
                        <li class="breadcrumb-item active">Users</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <div class='row'>
                                <div class='col'></div>
                                <div class='col text-right'>
                                    @can('Add Users')
                                    <div class="dropdown">
                                        <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-expanded="false">
                                            <i class='fas fa-plus'></i> Add New
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item btn-dropdown-member" href="#"><i class="fas fa-user-plus mr-2"></i> New Member</a>
                                            <a class="dropdown-item btn-dropdown-community" href="#"><i class="fas fa-people-carry mr-2"></i> New Community</a>
                                            <a class="dropdown-item btn-dropdown-department" href="#"><i class="fas fa-sitemap mr-2"></i> New Department</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item btn-dropdown-invite" href="#"><i class="fas fa-paper-plane mr-2"></i> Invite User</a>
                                            <a class="dropdown-item" href="{{ url('dashboard/users/invitations') }}"><i class="fas fa-list-alt mr-2"></i> View Invitations</a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#" data-toggle="modal" data-target="#importUsersModal"><i class="fas fa-file-import mr-2"></i> Import from Excel</a>
                                        </div>
                                    </div>
                                    @endcan
                                </div>
                            </div>
                        </div>
                        <div class="card-header">
                            <form class='row' id='search-form'>
                                <input type='hidden' value='1' name='visible'>
                                <div class="col-sm-4">
                                    <input type="text" class="form-control mb-1" name="search" placeholder="Search">
                                </div>
                                <div class='col-sm-4'>
                                    <select name="role" class='form-control mb-2' id='search-roles'></select>
                                </div>
                                <div class="col-sm-4">
                                    <select name="status" class="form-control mb-1">
                                        <option value='1'>Active</option>
                                        <option value='0'>In-Active</option>
                                        <option value='archived'>Archived</option>
                                    </select>
                                </div>
                            </form>
                        </div>
                        <div class='card-body'>
                            <div class="table-responsive">
                                <table class='table w-100'>
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th class='text-end'>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== ADD/EDIT USER MODAL ===== -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class='fas fa-user-plus'></i> <span>New Member</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="user-form">
                        @csrf
                        <input type='hidden' name='id' value='0'>

                        <!-- Required Fields -->
                        <div class="row">
                            <div class='col-sm-4 form-group'>
                                <label>First Name</label>
                                <input type='text' placeholder="First Name" name="firstname" class='form-control' autofocus required />
                            </div>
                            <div class='col-sm-4 form-group'>
                                <label>Last Name</label>
                                <input type='text' placeholder="Last Name" name="lastname" class='form-control' required />
                            </div>
                            <div class='col-sm-4 form-group'>
                                <label>Surname <small class="text-muted">(Middle Name)</small></label>
                                <input type='text' placeholder="Surname" name="surname" class='form-control' />
                            </div>
                            <div class='col-sm-6 form-group'>
                                <label>Email Address <small class="text-muted">(Optional)</small></label>
                                <input type='email' placeholder="Email Address" name="email" class='form-control' />
                            </div>
                            <div class='col-sm-6 form-group'>
                                <label>Phone Number</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text font-weight-bold">+{{ $site_settings->phone_code ?? '254' }}</span>
                                    </div>
                                    <input type='text' placeholder="e.g. 712345678" name="phone" class='form-control' required />
                                </div>
                            </div>
                            <div class='col-sm-6 form-group'>
                                <label>Status</label>
                                <select name="status" class='form-control'>
                                    <option value='1'>Active</option>
                                    <option value='0'>In-Active</option>
                                </select>
                            </div>
                        </div>

                        <!-- Advanced Details Toggle -->
                        <div class="mt-2 mb-2">
                            <a class="btn btn-sm btn-outline-secondary" data-toggle="collapse" href="#advancedFields" role="button" aria-expanded="false">
                                <i class="fas fa-chevron-down"></i> Advanced Details
                            </a>
                        </div>

                        <!-- Advanced Details - Tabbed -->
                        <div class="collapse" id="advancedFields">
                            <ul class="nav nav-tabs nav-sm" id="advTabs">
                                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#adv-personal"><i class="fas fa-user"></i> <span class="d-none d-sm-inline">Personal</span></a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#adv-contact"><i class="fas fa-phone"></i> <span class="d-none d-sm-inline">Contact</span></a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#adv-church"><i class="fas fa-church"></i> <span class="d-none d-sm-inline">Church</span></a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#adv-family"><i class="fas fa-users"></i> <span class="d-none d-sm-inline">Family</span></a></li>
                                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#adv-work"><i class="fas fa-briefcase"></i> <span class="d-none d-sm-inline">Work & Edu</span></a></li>
                            </ul>
                            <div class="tab-content border border-top-0 rounded-bottom p-3">
                                <!-- Personal Tab -->
                                <div class="tab-pane fade show active" id="adv-personal">
                                    <div class="row">
                                        <div class='col-sm-6 form-group'>
                                            <label>Gender</label>
                                            <select name="gender" class='form-control'>
                                                <option value="">-- Select --</option>
                                                <option value="0">Male</option>
                                                <option value="1">Female</option>
                                            </select>
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Date of Birth</label>
                                            <input type='text' placeholder="Date of Birth" name="dob" class='form-control modal-datepicker' readonly />
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Marital Status</label>
                                            <select name="marital" class='form-control'>
                                                <option value="">-- Select --</option>
                                                <option value="1">Single</option>
                                                <option value="2">Married</option>
                                                <option value="3">Divorced</option>
                                                <option value="4">Separated</option>
                                                <option value="5">Widow(er)</option>
                                            </select>
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Nationality</label>
                                            <select name="country" class='form-control'>
                                                <option value="">-- Select --</option>
                                                <option value="1">Kenya</option>
                                                <option value="2">Uganda</option>
                                                <option value="3">Tanzania</option>
                                                <option value="4">Burundi</option>
                                                <option value="5">Rwanda</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contact Tab -->
                                <div class="tab-pane fade" id="adv-contact">
                                    <div class="row">
                                        <div class='col-sm-6 form-group'>
                                            <label>Secondary Phone</label>
                                            <input type='text' placeholder="e.g. 0712345678" name="secondary_phone" class='form-control' />
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Home Town</label>
                                            <input type='text' placeholder="Home Town" name="town" class='form-control' />
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Postal Address</label>
                                            <textarea name="postal" placeholder="Postal Address" rows="2" class='form-control'></textarea>
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Residential Location</label>
                                            <select name="resident" class='form-control' id='modal-resident-select'></select>
                                        </div>
                                    </div>
                                </div>

                                <!-- Church Tab -->
                                <div class="tab-pane fade" id="adv-church">
                                    <div class="row">
                                        <div class='col-sm-6 form-group'>
                                            <label>Community</label>
                                            <select name="community" class='form-control'>
                                                <option value="">-- Select --</option>
                                                @foreach($communities as $c)
                                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Department</label>
                                            <select name="department" class='form-control'>
                                                <option value="">-- Select --</option>
                                                @foreach($departments as $d)
                                                    <option value="{{ $d->id }}">{{ $d->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class='col-sm-4 form-group'>
                                            <label>Year Joined</label>
                                            <select name="year_joined" class='form-control'>
                                                <option value="">-- Select --</option>
                                                @php $yr = date('Y'); @endphp
                                                @for($y = $yr; $y >= $yr - 10; $y--)
                                                    <option value="{{ $y }}">{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class='col-sm-4 form-group'>
                                            <label>Gifts / Ministry</label>
                                            <input type='text' placeholder="Gifts" name="gifts" class='form-control' />
                                        </div>
                                        <div class='col-sm-4 form-group'>
                                            <label>Previous Church</label>
                                            <input type='text' placeholder="Previous Church" name="previous_church" class='form-control' />
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Position Held</label>
                                            <input type='text' placeholder="Position" name="position" class='form-control' />
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Remarks</label>
                                            <textarea name="remarks" placeholder="Remarks" rows="2" class='form-control'></textarea>
                                        </div>
                                    </div>
                                </div>

                                <!-- Family Tab -->
                                <div class="tab-pane fade" id="adv-family">
                                    <div class="row">
                                        <div class='col-sm-6 form-group'>
                                            <label>Relationship</label>
                                            <select name="relationship" class='form-control'>
                                                <option value="">-- Select --</option>
                                                <option value="1">Spouse</option>
                                                <option value="2">Child</option>
                                                <option value="3">Sibling</option>
                                                <option value="4">Parent</option>
                                                <option value="5">Guardian</option>
                                                <option value="6">Next of Kin</option>
                                            </select>
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Name</label>
                                            <input type='text' placeholder="Family Member Name" name="family_name" class='form-control' />
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Phone</label>
                                            <input type='text' placeholder="e.g. 254712345678" name="family_phone" class='form-control' />
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Email</label>
                                            <input type='email' placeholder="Email" name="family_email" class='form-control' />
                                        </div>
                                    </div>
                                </div>

                                <!-- Work & Edu Tab -->
                                <div class="tab-pane fade" id="adv-work">
                                    <h6 class="text-muted mb-3"><i class="fas fa-briefcase mr-1"></i> Employment</h6>
                                    <div class="row">
                                        <div class='col-sm-6 form-group'>
                                            <label>Occupation Industry</label>
                                            <select name="occupation" class='form-control'>
                                                <option value="">-- Select --</option>
                                                <option value="1">Accommodation & Food Services</option>
                                                <option value="2">Arts and Entertainment</option>
                                                <option value="3">Corporate Admin & Management</option>
                                                <option value="4">Educational Services</option>
                                                <option value="5">Engineering, Scientific & Technical</option>
                                                <option value="6">Finance and Insurance</option>
                                                <option value="7">Food, Agriculture and Forestry</option>
                                                <option value="8">Government</option>
                                                <option value="9">Health Care & Social Assistance</option>
                                                <option value="10">ICT</option>
                                                <option value="11">Janitorial, Waste Management</option>
                                                <option value="12">Manufacturing & Processing</option>
                                                <option value="13">Mining, Quarrying, Oil & Gas</option>
                                                <option value="14">Public Administration</option>
                                                <option value="15">Public Utility Services</option>
                                                <option value="16">Real Estate, Rental & Leasing</option>
                                                <option value="17">Religion</option>
                                                <option value="18">Retail Trade</option>
                                                <option value="19">Sports and Recreation</option>
                                                <option value="20">Security & Armed Forces</option>
                                                <option value="21">Transportation & Warehousing</option>
                                                <option value="22">Wholesale Trade</option>
                                                <option value="23">None</option>
                                                <option value="24">Other</option>
                                            </select>
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>Specific Job</label>
                                            <input type='text' placeholder="Specific Job" name="specific_job" class='form-control' />
                                        </div>
                                        <div class='col-sm-4 form-group'>
                                            <label>Institution</label>
                                            <input type='text' placeholder="Institution" name="work_institution" class='form-control' />
                                        </div>
                                        <div class='col-sm-4 form-group'>
                                            <label>From</label>
                                            <input type='text' placeholder="From" name="work_from" class='form-control modal-datepicker' readonly />
                                        </div>
                                        <div class='col-sm-4 form-group'>
                                            <label>To</label>
                                            <input type='text' placeholder="To" name="work_to" class='form-control modal-datepicker' readonly />
                                        </div>
                                    </div>
                                    <hr>
                                    <h6 class="text-muted mb-3"><i class="fas fa-graduation-cap mr-1"></i> Education</h6>
                                    <div class="row">
                                        <div class='col-sm-4 form-group'>
                                            <label>Level</label>
                                            <select name="edu_level" class='form-control'>
                                                <option value="">-- Select --</option>
                                                <option value="1">Basic</option>
                                                <option value="2">O'Level</option>
                                                <option value="3">A'Level</option>
                                                <option value="4">Sec./Tech/Vocational</option>
                                                <option value="5">Post Secondary Non-tertiary</option>
                                                <option value="6">Diploma or Equivalent</option>
                                                <option value="7">Degree or Equivalent</option>
                                                <option value="8">Masters or Equivalent</option>
                                                <option value="9">Doctoral or Equivalent</option>
                                            </select>
                                        </div>
                                        <div class='col-sm-4 form-group'>
                                            <label>Institution</label>
                                            <input type='text' placeholder="Institution" name="edu_institution" class='form-control' />
                                        </div>
                                        <div class='col-sm-4 form-group'>
                                            <label>Certification</label>
                                            <input type='text' placeholder="Certification" name="certification" class='form-control' />
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>From</label>
                                            <input type='text' placeholder="From" name="edu_from" class='form-control modal-datepicker' readonly />
                                        </div>
                                        <div class='col-sm-6 form-group'>
                                            <label>To</label>
                                            <input type='text' placeholder="To" name="edu_to" class='form-control modal-datepicker' readonly />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class='col-sm-12 mt-2'>
                            <div class='alert feedback border d-none'>
                                <i class='fas fa-spinner fa-pulse'></i> Saving... Please wait
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class='fas fa-times'></i> Close</button>
                    <button type="button" class="btn btn-primary btn-sm btnSaveUser"><i class='fas fa-paper-plane'></i> Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== COMMUNITY / DEPARTMENT MODAL ===== -->
    <div class="modal fade" id="groupModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class='fas fa-people-carry'></i> <span>New Community</span></h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ url('dashboard/people/add') }}" enctype="multipart/form-data" id="group-form">
                        @csrf
                        <input type="hidden" name="id" value="0">
                        <input type="hidden" name="people" value="1">
                        <input type="hidden" name="user_id" value="0">

                        <div class="text-center mb-3">
                            <img src="{{ asset('website/default.jpg') }}" class="img-fluid rounded mb-2" id="group-photo-preview" style="max-height:120px;">
                            <div>
                                <a href="#" class="btn btn-outline-primary btn-sm" id="group-photo-btn">
                                    <i class="fas fa-camera"></i> Upload Image
                                </a>
                                <input type="file" name="photo" class="d-none" id="group-photo-input" accept="image/*">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Leader</label>
                            <select name="leader_select" class='form-control' id='leader-select'></select>
                        </div>
                        <div class='form-group'>
                            <label>Name</label>
                            <input type='text' placeholder="Name" name="name" class='form-control' required />
                        </div>
                        <div class='form-group'>
                            <label>Description</label>
                            <textarea name="description" placeholder="Description" rows="3" class='form-control' required></textarea>
                        </div>
                        <div class='alert group-feedback border d-none'></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class='fas fa-times'></i> Close</button>
                    <button type="button" class="btn btn-primary btn-sm btnSaveGroup"><i class='fas fa-paper-plane'></i> Save</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== INVITE USER MODAL ===== -->
    <div class="modal fade" id="inviteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class='fas fa-paper-plane'></i> Invite User</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form id="invite-form">
                        @csrf
                        <div class="form-group">
                            <label>Invite via</label>
                            <select name="method" class="form-control" id="invite-method">
                                <option value="sms">SMS</option>
                                <option value="email">Email</option>
                            </select>
                        </div>
                        <div class="form-group" id="invite-phone-group">
                            <label>Phone Number</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">+{{ $site_settings->phone_code ?? '254' }}</span>
                                </div>
                                <input type="text" name="phone" class="form-control" placeholder="e.g. 712345678" id="invite-phone-input">
                            </div>
                        </div>
                        <div class="form-group d-none" id="invite-email-group">
                            <label>Email Address</label>
                            <input type="email" name="email" class="form-control" placeholder="example@email.com">
                        </div>
                        <div id="invite-phone-check" class="d-none mb-3"></div>
                        <div class='alert invite-feedback border d-none'></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class='fas fa-times'></i> Close</button>
                    <button type="button" class="btn btn-success btn-sm btnSendInvite"><i class='fas fa-paper-plane'></i> Send Invite</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== IMPORT USERS MODAL ===== -->
    <div class="modal fade" id="importUsersModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class='fas fa-file-import'></i> Import Members</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <form action="{{ url('dashboard/users/import') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-2">
                            <label>Upload an Excel file with columns: <strong>First Name, Surname, Last Name, Phone, Email, Gender, Date of Birth</strong></label>
                            <a href="{{ asset('samples/members_import_sample.xlsx') }}" class="btn btn-sm btn-outline-success"><i class="fas fa-download"></i> Download Sample File</a>
                        </div>
                        <div class="form-group">
                            <label>Excel File</label>
                            <input type="file" class="form-control" name="import_file" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">Max 5MB. Duplicate phone numbers will be skipped.</small>
                        </div>
                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-file-upload"></i> Import</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== SMS MODAL ===== -->
    <div class="modal fade" id="smsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class='fas fa-sms'></i> Send SMS</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="sms-user-id" value="0">
                    <div class="form-group">
                        <label>To</label>
                        <input type="text" class="form-control" id="sms-recipient" readonly>
                    </div>
                    <div class="form-group">
                        <label>Message</label>
                        <textarea class="form-control" id="sms-message" rows="4" placeholder="Type your message here"></textarea>
                        <small class="text-muted"><span id="sms-msg-char-count">0</span> characters</small>
                    </div>
                    <div class='alert sms-feedback border d-none'></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class='fas fa-times'></i> Close</button>
                    <button type="button" class="btn btn-success btn-sm btnSendSms"><i class='fas fa-paper-plane'></i> Send SMS</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
$(document).ready(function () {
    const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone;

    flatpickr(".modal-datepicker", { dateFormat: "Y-m-d" });

    // ===== DataTable =====
    var table = $('.table').DataTable({
        scrollX: true,
        fixedColumns: { right: 1 },
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('dashboard/users/datatable') }}",
            data: function (d) {
                d.search = $('#search-form input[name=search]').val();
                d.role = $('#search-form select[name=role]').val();
                d.status = $('#search-form select[name=status]').val();
                d.timezone = timeZone;
            }
        },
        dom: 'lBtrip',
        language: { emptyTable: "<i class='fas fa-ban'></i> No Users available" },
        columns: [
            { data: 'name', name: 'name', orderable: false, searchable: false },
            { data: 'email', name: 'email', orderable: false, searchable: false },
            { data: 'phone', name: 'phone', orderable: false, searchable: false },
            { data: 'role', name: 'role', orderable: false, searchable: false },
            { data: 'status', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        createdRow: function (row, data) {
            $(row).css('cursor', 'pointer');
            $(row).attr('data-user-id', data.id);
        }
    });

    // Make rows clickable to view user (ignore clicks on action buttons/dropdowns)
    $('.table tbody').on('click', 'tr', function (e) {
        if ($(e.target).closest('.dropdown, button, a, .btn').length) return;
        var userId = $(this).data('user-id');
        if (userId) {
            window.location.href = '{{ url("dashboard/users/view") }}/' + userId;
        }
    });

    var timer = null;
    $('#search-form input[name=search]').keyup(function () {
        clearTimeout(timer);
        timer = setTimeout(function () { table.draw(); }, 1000);
    });
    $('#search-form select').change(function () { table.draw(); });

    // ===== Search Roles Select2 =====
    $('#search-roles').select2({
        placeholder: 'Select Role', allowClear: true,
        ajax: {
            url: '{{ url("dashboard/search/roles") }}', dataType: 'json', delay: 250,
            processResults: function (data) {
                return { results: $.map(data, function (item) { return { text: item.name, id: item.id }; }) };
            }, cache: true
        }
    });

    // ===== Leader Select2 =====
    $('#leader-select').select2({
        width: '100%', placeholder: 'Select Leader', dropdownParent: $('#groupModal'), allowClear: true,
        ajax: {
            url: '{{ url("dashboard/people/users") }}', dataType: 'json', delay: 250,
            data: function (params) { return { search: params.term }; },
            processResults: function (data) {
                return { results: $.map(data, function (item) { return { text: item.firstname + ' ' + item.lastname, id: item.id }; }) };
            }, cache: true
        }
    });

    // ===== Modal Residence Select2 =====
    $('#modal-resident-select').select2({
        width: '100%', placeholder: 'Select Residence', dropdownParent: $('#userModal'), allowClear: true, tags: true,
        ajax: {
            url: '{{ url("dashboard/settings/residences/search") }}', dataType: 'json', delay: 250,
            data: function (params) { return { q: params.term }; },
            processResults: function (data) {
                return { results: $.map(data, function (item) { return { text: item.name, id: item.name }; }) };
            }, cache: true
        }
    });

    // ===== Dropdown Menu Actions =====
    function resetUserForm() {
        $('#user-form')[0].reset();
        $('#user-form input[name=id]').val(0);
        $('#advancedFields').collapse('hide');
        $('#userModal .feedback').addClass('d-none').removeClass('alert-danger alert-success');
    }

    $('.btn-dropdown-member').click(function (e) {
        e.preventDefault();
        resetUserForm();
        $('#userModal .modal-title span').text('New Member');
        $('#userModal input[name=email], #userModal input[name=phone]').removeAttr('readonly');
        $('#userModal').modal('show');
    });

    $('.btn-dropdown-community').click(function (e) {
        e.preventDefault();
        $('#group-form')[0].reset();
        $('#group-form input[name=id]').val(0);
        $('#group-form input[name=people]').val(1);
        $('#leader-select').empty();
        $('#group-photo-preview').attr('src', '{{ asset("website/default.jpg") }}');
        $('#groupModal .modal-title span').text('New Community');
        $('#groupModal').modal('show');
    });

    $('.btn-dropdown-department').click(function (e) {
        e.preventDefault();
        $('#group-form')[0].reset();
        $('#group-form input[name=id]').val(0);
        $('#group-form input[name=people]').val(2);
        $('#leader-select').empty();
        $('#group-photo-preview').attr('src', '{{ asset("website/default.jpg") }}');
        $('#groupModal .modal-title span').text('New Department');
        $('#groupModal').modal('show');
    });

    $('.btn-dropdown-invite').click(function (e) {
        e.preventDefault();
        $('#invite-form')[0].reset();
        $('#invite-method').val('sms').trigger('change');
        $('#inviteModal .invite-feedback').addClass('d-none').removeClass('alert-danger alert-success');
        $('#invite-phone-check').addClass('d-none').html('');
        $('#inviteModal').modal('show');
    });

    // ===== Photo Upload =====
    $('#group-photo-btn').click(function(e) { e.preventDefault(); $('#group-photo-input').click(); });
    $('#group-photo-input').change(function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) { $('#group-photo-preview').attr('src', e.target.result); };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // ===== Invite Method Toggle =====
    $('#invite-method').change(function () {
        if ($(this).val() === 'sms') {
            $('#invite-phone-group').removeClass('d-none');
            $('#invite-email-group').addClass('d-none');
        } else {
            $('#invite-phone-group').addClass('d-none');
            $('#invite-email-group').removeClass('d-none');
        }
        $('#invite-phone-check').addClass('d-none').html('');
    });

    // ===== Phone Number Check (on blur) =====
    var phoneCheckTimer = null;
    $('#invite-phone-input').on('blur keyup', function (e) {
        var phone = $(this).val().replace(/[^0-9]/g, '');
        if (phone.length < 9) {
            $('#invite-phone-check').addClass('d-none').html('');
            return;
        }
        if (e.type === 'keyup') {
            clearTimeout(phoneCheckTimer);
            phoneCheckTimer = setTimeout(function () { doPhoneCheck(phone); }, 800);
        } else {
            doPhoneCheck(phone);
        }
    });

    function doPhoneCheck(phone) {
        $.ajax({
            url: '{{ url("dashboard/users/check-invite-phone") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', phone: phone }
        }).done(function (data) {
            var $box = $('#invite-phone-check');
            if (data.status === 'user_exists') {
                var html = '<div class="alert alert-' + (data.verified ? 'success' : 'warning') + ' mb-0 py-2 px-3">' +
                    '<i class="fas fa-' + (data.verified ? 'check-circle' : 'exclamation-triangle') + ' mr-1"></i> ' +
                    data.message + '<br>' +
                    '<a href="' + data.profile_url + '" class="btn btn-sm btn-outline-primary mt-1" target="_blank">' +
                    '<i class="fas fa-user mr-1"></i> View Profile</a></div>';
                $box.html(html).removeClass('d-none');
            } else if (data.status === 'previously_invited') {
                var html = '<div class="alert alert-info mb-0 py-2 px-3">' +
                    '<i class="fas fa-info-circle mr-1"></i> ' + data.message + '<br>' +
                    '<small class="text-muted">You can still send a new invitation.</small></div>';
                $box.html(html).removeClass('d-none');
            } else {
                $box.addClass('d-none').html('');
            }
        });
    }

    // ===== Save User =====
    $('.btnSaveUser').click(function () {
        var btn = $(this);
        btn.attr('disabled', 'disabled');
        $('#userModal .feedback').removeClass('d-none alert-danger alert-success')
            .html("<i class='fas fa-spinner fa-pulse'></i> Saving... Please wait");
        $.ajax({
            url: '{{ url("dashboard/users/add") }}',
            type: 'POST',
            data: $('#user-form').serialize()
        }).done(function (data) {
            $('#userModal .feedback').addClass('alert-success')
                .html("<i class='fas fa-check-circle'></i> " + data.success);
            table.draw();
            setTimeout(() => { $('#userModal .feedback').addClass('d-none'); }, 3000);
            btn.removeAttr('disabled');
        }).fail(function (response) {
            let data = response.responseJSON;
            $('#userModal .feedback').addClass('alert-danger').html("");
            if (data && data.errors) {
                $.each(data.errors, function(key, msgs) {
                    $.each(msgs, function(i, msg) {
                        $('#userModal .feedback').append("<i class='fas fa-exclamation-circle'></i> " + msg + "<br>");
                    });
                });
            } else if (data && data.error) {
                $('#userModal .feedback').html("<i class='fas fa-exclamation-circle'></i> " + data.error);
            } else {
                $('#userModal .feedback').html("<i class='fas fa-exclamation-circle'></i> Something went wrong!");
            }
            setTimeout(() => { $('#userModal .feedback').addClass('d-none'); }, 5000);
            btn.removeAttr('disabled');
        });
    });

    // ===== Save Group =====
    $('.btnSaveGroup').click(function () {
        var leaderId = $('#leader-select').val();
        if (!leaderId || leaderId == 0) {
            $('#groupModal .group-feedback').removeClass('d-none').addClass('alert-danger')
                .html("<i class='fas fa-exclamation-circle'></i> Please select a leader.");
            setTimeout(() => { $('#groupModal .group-feedback').addClass('d-none'); }, 3000);
            return;
        }
        $('#group-form input[name=user_id]').val(leaderId);
        $('#group-form')[0].submit();
    });

    // ===== Send Invite =====
    $('.btnSendInvite').click(function () {
        var btn = $(this);
        btn.attr('disabled', 'disabled');
        $('#inviteModal .invite-feedback').removeClass('d-none alert-danger alert-success')
            .html("<i class='fas fa-spinner fa-pulse'></i> Sending... Please wait");
        $.ajax({
            url: '{{ url("dashboard/users/invite") }}',
            type: 'POST',
            data: $('#invite-form').serialize()
        }).done(function (data) {
            $('#inviteModal .invite-feedback').addClass('alert-success')
                .html("<i class='fas fa-check-circle'></i> " + data.success);
            $('#invite-phone-check').addClass('d-none').html('');
            setTimeout(() => { $('#inviteModal .invite-feedback').addClass('d-none'); }, 3000);
            btn.removeAttr('disabled');
        }).fail(function (response) {
            let data = response.responseJSON;
            $('#inviteModal .invite-feedback').addClass('alert-danger').html("");
            if (data && data.errors) {
                $.each(data.errors, function(key, msgs) {
                    $.each(msgs, function(i, msg) {
                        $('#inviteModal .invite-feedback').append("<i class='fas fa-exclamation-circle'></i> " + msg + "<br>");
                    });
                });
            } else if (data && data.error) {
                var html = "<i class='fas fa-exclamation-circle'></i> " + data.error;
                if (data.profile_url) {
                    html += '<br><a href="' + data.profile_url + '" class="btn btn-sm btn-outline-primary mt-1" target="_blank"><i class="fas fa-user mr-1"></i> View Profile</a>';
                }
                $('#inviteModal .invite-feedback').html(html);
            } else {
                $('#inviteModal .invite-feedback').html("<i class='fas fa-exclamation-circle'></i> Something went wrong!");
            }
            setTimeout(() => { $('#inviteModal .invite-feedback').addClass('d-none'); }, 8000);
            btn.removeAttr('disabled');
        });
    });

    // ===== Edit User from Table Row =====
    $(document).on('click', '.table .btn-edit', function () {
        resetUserForm();
        $('#userModal .modal-title span').text("Edit User");
        $('#userModal input[name=email]').attr('readonly', 'readonly');

        var row = $(this).closest('tr');
        $('#user-form input[name=id]').val(row.find('.id').text());
        $('#user-form input[name=firstname]').val(row.find('.firstname').text());
        $('#user-form input[name=surname]').val(row.find('.surname').text());
        $('#user-form input[name=lastname]').val(row.find('.lastname').text());
        $('#user-form input[name=email]').val(row.find('.email').text());
        $('#user-form input[name=phone]').val(row.find('.phone').text());
        $('#user-form select[name=status]').val(row.find('.status').text());
        $('#userModal').modal('show');
    });

    // ===== Archive User =====
    $(document).on('click', '.table .btn-archive', function () {
        var row = $(this).closest('tr');
        var id = row.find('.id').text();
        var name = row.find('.firstname').text() + ' ' + row.find('.lastname').text();
        Swal.fire({
            title: 'Archive ' + name + '?',
            text: 'This user will be moved to the archived list.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f0ad4e',
            confirmButtonText: 'Yes, archive'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("dashboard/users/archive") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', id: id }
                }).done(function (data) {
                    Swal.fire('Archived!', data.success, 'success');
                    table.draw();
                }).fail(function (r) {
                    Swal.fire('Error', r.responseJSON?.error || 'Something went wrong', 'error');
                });
            }
        });
    });

    // ===== Unarchive User =====
    $(document).on('click', '.table .btn-unarchive', function () {
        var row = $(this).closest('tr');
        var id = row.find('.id').text();
        $.ajax({
            url: '{{ url("dashboard/users/unarchive") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', id: id }
        }).done(function (data) {
            Swal.fire('Unarchived!', data.success, 'success');
            table.draw();
        }).fail(function (r) {
            Swal.fire('Error', r.responseJSON?.error || 'Something went wrong', 'error');
        });
    });

    // ===== Delete User Permanently =====
    $(document).on('click', '.table .btn-delete-permanent', function () {
        var row = $(this).closest('tr');
        var id = row.find('.id').text();
        var name = row.find('.firstname').text() + ' ' + row.find('.lastname').text();
        Swal.fire({
            title: 'Permanently delete ' + name + '?',
            text: 'This action cannot be undone. All user data will be removed.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete permanently'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("dashboard/users/delete") }}',
                    type: 'POST',
                    data: { _token: '{{ csrf_token() }}', id: id }
                }).done(function (data) {
                    Swal.fire('Deleted!', data.success, 'success');
                    table.draw();
                }).fail(function (r) {
                    Swal.fire('Error', r.responseJSON?.error || 'Something went wrong', 'error');
                });
            }
        });
    });

    // ===== SMS Modal =====
    $(document).on('click', '.table .btn-sms', function () {
        var row = $(this).closest('tr');
        $('#sms-user-id').val(row.find('.id').text());
        $('#sms-recipient').val(row.find('.firstname').text() + ' ' + row.find('.lastname').text() + ' (' + row.find('.phone').text() + ')');
        $('#sms-message').val('');
        $('#sms-msg-char-count').text('0');
        $('#smsModal .sms-feedback').addClass('d-none').removeClass('alert-danger alert-success');
    });

    $('#sms-message').on('input', function() { $('#sms-msg-char-count').text($(this).val().length); });

    $('.btnSendSms').click(function () {
        var btn = $(this);
        var message = $.trim($('#sms-message').val());
        if (message.length === 0) {
            $('#smsModal .sms-feedback').removeClass('d-none').addClass('alert-danger')
                .html("<i class='fas fa-exclamation-circle'></i> Please enter a message.");
            setTimeout(() => { $('#smsModal .sms-feedback').addClass('d-none'); }, 3000);
            return;
        }
        btn.attr('disabled', 'disabled');
        $('#smsModal .sms-feedback').removeClass('d-none alert-danger alert-success')
            .html("<i class='fas fa-spinner fa-pulse'></i> Sending...");
        $.ajax({
            url: '{{ url("dashboard/users/sendsms") }}',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', user_id: $('#sms-user-id').val(), message: message }
        }).done(function (data) {
            $('#smsModal .sms-feedback').addClass('alert-success')
                .html("<i class='fas fa-check-circle'></i> " + data.success);
            $('#sms-message').val('');
            $('#sms-msg-char-count').text('0');
            setTimeout(() => { $('#smsModal .sms-feedback').addClass('d-none'); }, 3000);
            btn.removeAttr('disabled');
        }).fail(function (response) {
            let data = response.responseJSON;
            $('#smsModal .sms-feedback').addClass('alert-danger');
            if (data && data.error) {
                $('#smsModal .sms-feedback').html("<i class='fas fa-exclamation-circle'></i> " + data.error);
            } else {
                $('#smsModal .sms-feedback').html("<i class='fas fa-exclamation-circle'></i> Something went wrong!");
            }
            setTimeout(() => { $('#smsModal .sms-feedback').addClass('d-none'); }, 5000);
            btn.removeAttr('disabled');
        });
    });
});
</script>
@endpush
