<div class="app-sidebar sidebar-shadow">
    <div class="app-header__logo">
        <div class="logo-src"></div>
        <div class="header__pane ml-auto">
            <div>
                <button type="button" class="hamburger close-sidebar-btn hamburger--elastic" data-class="closed-sidebar">
                    <span class="hamburger-box">
                        <span class="hamburger-inner"></span>
                    </span>
                </button>
            </div>
        </div>
    </div>
    <div class="app-header__mobile-menu">
        <div>
            <button type="button" class="hamburger hamburger--elastic mobile-toggle-nav">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>
    </div>
    <div class="app-header__menu">
        <span>
            <button type="button" class="btn-icon btn-icon-only btn btn-primary btn-sm mobile-toggle-header-nav">
                <span class="btn-icon-wrapper">
                    <i class="fa fa-ellipsis-v fa-w-6"></i>
                </span>
            </button>
        </span>
    </div>
    <div class="scrollbar-sidebar">
        <div class="app-sidebar__inner">
            <ul class="vertical-nav-menu">
                {{-- @if (Auth::user()->is_admin !== 0) --}}

                {{-- {{ Auth::user()->avatar }} --}}
                <li class="app-sidebar__heading">Dashboards</li>
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ Request::is('dashboard') ? 'mm-active' : '' }}">
                        <i class="metismenu-icon pe-7s-rocket"></i>
                        Dashboard
                    </a>
                </li>
                {{-- @endif --}}
                <li class="app-sidebar__heading">Sales</li>
                @if (Auth::user()->is_admin !== 0)
                    <li>
                        <a href="#"
                            class="{{ Request::is('sales/list') || Request::is('sales/new') || Request::is('sales/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Sales
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('saleslist') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('salesReturn/list') || Request::is('salesReturn/new') || Request::is('salesReturn/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Sales return
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('saleReturnList') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                <li>
                    <a href="#"
                        class="{{ Request::is('customer/list') || Request::is('customer/new') || Request::is('customerreceipt/list') || Request::is('customer/edit*') || Request::is('customerreceipt/edit*') || Request::is('customerreceipt/new') ? 'mm-active' : '' }}">
                        <i class="metismenu-icon pe-7s-users"></i>
                        Customers
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('customerlist') }}">
                                <i class="metismenu-icon"></i>
                                List
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('customer_receiptlist') }}">
                                <i class="metismenu-icon"></i>
                                Customers Receipt
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="#"
                        class="{{ Request::is('quotation/list') || Request::is('quotation/new') || Request::is('quotation/edit/*') || Request::is('quotation/update') ? 'mm-active' : '' }}">
                        <i class="metismenu-icon pe-7s-users"></i>
                        Quotation
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('quotationList') }}">
                                <i class="metismenu-icon"></i>
                                List
                            </a>
                        </li>
                    </ul>
                    <ul>
                        <li>
                            <a href="{{ route('PackagesList') }}">
                                <i class="metismenu-icon"></i>
                                Packages
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="app-sidebar__heading">Daily Visits</li>
                <li>
                    <a href="#"
                        class="{{ Request::is('dailyVisit/list') || Request::is('dailyVisit/new') || Request::is('dailyVisit/edit*') ? 'mm-active' : '' }}">
                        <i class="metismenu-icon pe-7s-users"></i>
                        Daily Visits
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('dailyVisitList') }}">
                                <i class="metismenu-icon"></i>
                                List
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="app-sidebar__heading">Catalogs</li>
                <li>
                    {{-- || Request::is('dailyVisit/new') || Request::is('dailyVisit/edit*') --}}
                    <a href="#" class="{{ Request::is('catalog/list') ? 'mm-active' : '' }}">
                        <i class="metismenu-icon pe-7s-users"></i>
                        Catalogs
                        <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                    </a>
                    <ul>
                        <li>
                            <a href="{{ route('catalogList') }}">
                                <i class="metismenu-icon"></i>
                                List
                            </a>
                        </li>
                    </ul>
                </li>
                @if (Auth::user()->is_admin !== 0)
                    <li class="app-sidebar__heading">Terms & Conditions</li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('termCondition/list') || Request::is('termCondition/new') || Request::is('termCondition/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Terms & Conditions
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('termConditionList') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if (Auth::user()->is_admin !== 0)
                    <li class="app-sidebar__heading">Purchase</li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('purchases/purchaselist') || Request::is('purchases/new') || Request::is('purchases/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Purchases
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('purchaseList') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('purchasesReturn/list') || Request::is('purchasesReturn/new') || Request::is('purchasesReturn/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Purchases Return
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('purchaseReturnList') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('vendor/list') || Request::is('vendor/new') || Request::is('vendor/edit*') || Request::is('vendorpayment/list') || Request::is('vendorpayment/new') || Request::is('vendorpayment/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Vendor
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('vendorlist') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('vendorpaymentlist') }}">
                                    <i class="metismenu-icon"></i>
                                    Vendor payment
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="app-sidebar__heading">User Managment</li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('user/list') || Request::is('user/new') || Request::is('user/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Users
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('userlist') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="app-sidebar__heading">Menu Managment</li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('menu/menuList') || Request::is('menu/newmenu') || Request::is('menu/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Menues
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('menulist') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="app-sidebar__heading">Company Managment</li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('company/comlist') || Request::is('company/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Companies
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('companylist') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if (Auth::user()->is_admin !== 0)
                    <li class="app-sidebar__heading">Inventory Managment</li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('category/categoryList') || Request::is('category/newcategory') || Request::is('category/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Category
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('categorylist') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('item/itemList') || Request::is('item/newitem') || Request::is('item/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Items
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('itemlist') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if (Auth::user()->is_admin !== 0)
                    <li class="app-sidebar__heading">Employee Managment</li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('department/departmentList') || Request::is('department/newdepartment') || Request::is('department/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Departements
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('departmentlist') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('employee/employeeList') || Request::is('employee/newemployee') || Request::is('employee/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Employee
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('employeelist') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('employeePayments/list') || Request::is('employeePayments/new') || Request::is('employeePayments/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Employee Payments
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('employeePaymentList') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('advanceReturn/list') || Request::is('advanceReturn/new') || Request::is('advanceReturn/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Advance Return
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('advanceReturnList') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('employee/employeeAttendeelist') || Request::is('employee/employeeAttendeeAdd') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Employee Attendee
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <!-- <li>
                            <a href="{{ route('employeeAttendeeList') }}">
                                <i class="metismenu-icon"></i>
                                list
                            </a>
                        </li> -->
                            <li>
                                <a href="{{ route('employeeAttendeeList') }}">
                                    <i class="metismenu-icon"></i>
                                    list
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="app-sidebar__heading">Production Managment</li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('production/productionList') || Request::is('production/newproduction') || Request::is('production/productionReport') || Request::is('production/groupItemproductionReport') || Request::is('production/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Production
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('productionlist') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('productionReport') }}">
                                    <i class="metismenu-icon"></i>
                                    Report
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('groupItemproductionReport') }}">
                                    <i class="metismenu-icon"></i>
                                    Group Item Production Report
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('workinprocess/list') || Request::is('workinprocess/new') || Request::is('workinprocess/edit*') || Request::is('spoilageOrLoss/list') || Request::is('spoilageOrLoss/new') || Request::is('spoilageOrLoss/edit*') || Request::is('finishedGoods/list') || Request::is('finishedGoods/new') || Request::is('finishedGoods/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Working Process
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('workProcessList') }}">
                                    <i class="metismenu-icon"></i>
                                    Work in process
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('spoilageOrLoss') }}">
                                    <i class="metismenu-icon"></i>
                                    Spoilage/loss
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('finishedGood') }}">
                                    <i class="metismenu-icon"></i>
                                    Finished goods
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="app-sidebar__heading">General Entries</li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('ledgerAccounts/list') || Request::is('ledgerAccounts/new') || Request::is('ledgerAccounts/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Ledger Accounts
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('ledgerAccountsList') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('generalReciepts/list') || Request::is('generalReciepts/new') || Request::is('generalReciepts/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            General Receipts
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('generalReceiptsList') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('generalPayment/list') || Request::is('generalPayment/new') || Request::is('generalPayment/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            General Payments
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('generalPaymentList') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('journalVoucher/list') || Request::is('journalVoucher/new') || Request::is('journalVoucher/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Journal Voucher
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('journalVoucherList') }}">
                                    <i class="metismenu-icon"></i>
                                    List
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('balanceSheet/list') || Request::is('incomeStatement/list') || Request::is('generalReports/accountReceivable') || Request::is('generalReports/accountPayable') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            General Reports
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('balanceSheet') }}">
                                    <i class="metismenu-icon"></i>
                                    Balance Sheet
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('incomeStatement') }}">
                                    <i class="metismenu-icon"></i>
                                    Income Statment
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('accountReceivable') }}">
                                    <i class="metismenu-icon"></i>
                                    Accounts Receivable
                                </a>
                            </li>
                            <li>
                                <a href="{{ route('accountPayable') }}">
                                    <i class="metismenu-icon"></i>
                                    Accounts Payable
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="app-sidebar__heading">To Do</li>
                    <li>
                        <a href="#"
                            class="{{ Request::is('todo/list') || Request::is('todo/new') || Request::is('todo/edit*') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            To Do
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('toDoList') }}">
                                    <i class="metismenu-icon"></i>
                                    list
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="app-sidebar__heading">Transaction Log</li>
                    <li>
                        <a href="#" class="{{ Request::is('transactionLog/list') ? 'mm-active' : '' }}">
                            <i class="metismenu-icon pe-7s-users"></i>
                            Transaction Log
                            <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i>
                        </a>
                        <ul>
                            <li>
                                <a href="{{ route('transactionLogList') }}">
                                    <i class="metismenu-icon"></i>
                                    list
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="app-sidebar__heading">Database Backup</li>
                    <li>
                        <a href="{{ route('databaseBackup') }}" class="">
                            <i class="metismenu-icon pe-7s-box2"></i>
                            Backup
                            <!-- <i class="metismenu-state-icon pe-7s-angle-down caret-left"></i> -->
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
