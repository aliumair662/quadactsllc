var toasterTitle = "Well done!";
var toasterMessage = "Some thing went wrong";

function successToaster() {
    $(".toast").addClass("toast-success");
    $(".toast-title").text(toasterTitle);
    $(".toast-message").text(toasterMessage);
    $("#toast-container").show(300, "swing");
}

function errorToaster() {
    $(".toast").addClass("toast-error");
    $(".toast-title").text(toasterTitle);
    $(".toast-message").text(toasterMessage);
    $("#toast-container").show(300, "swing");
}

function onloader() {
    document.getElementById("overlay").style.display = "flex";
}

function offloader() {
    document.getElementById("overlay").style.display = "none";
}

function redirectUrl(url) {
    setTimeout(function () {
        window.location.href = url;
    }, 2000);
}

function pcsSum() {
    let item_pcs = 0;
    $(".item_pcs").each(function () {
        item_pcs += Number($(this).val());
    });
    $("#pcs").val(item_pcs);
}

function qtySum() {
    let item_pcs = 0;
    $(".item_qt").each(function () {
        item_pcs += Number($(this).val());
    });
    $("#qty_").val(item_pcs);
}

function checkDebitCredit() {
    var credit1 = $(".credit1").val();
    var credit2 = $(".credit2").val();
    var debit1 = $(".debit1").val();
    var debit2 = $(".debit2").val();
    if (credit1 !== "") {
        $(".credit2").val("");
    } else if (credit2 !== "") {
        $(".credit1").val("");
    }

    if (debit1 !== "") {
        $(".debit2").val("");
    } else if (debit2 !== "") {
        $(".debit1").val("");
    }

    if (
        (credit1 !== "" && debit1 !== "") ||
        (credit2 !== "" && debit2 !== "")
    ) {
        $(".credit1").val("");
        $(".credit2").val("");
        $(".debit1").val("");
        $(".debit2").val("");
    }
}

async function fetchingItemData($this) {
    let url = `${window.location.origin}/item/data`;
    let id = $($this).closest("tr").find(".itemData").val();
    $.ajax({
        type: "POST",
        url: url,
        data: { id: id },
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: await function (data) {
            console.log(data.data);
            $($this)
                .closest("tr")
                .find(".item_purchase_price")
                .val(data.data.purchase_price);
            $($this)
                .closest("tr")
                .find(".item_price")
                .val(data.data.sele_price);
            $($this).closest("tr").find(".item_name").val(data.data.name);
            calculateInvoiceSum();
            calculatePurchaseAmountSum();
            calculateNetProfit();
        },
        error: function (err) {
            console.log(err);
        },
    });
}
function calculateInvoiceSum() {
    var net_total = 0;
    $(".item_price").each(function (index) {
        var item_price = $(this).closest("tr").find(".item_price").val();
        if ($(this).closest("tr").find(".additional_rate").length) {
            item_price = (
                parseFloat(item_price) +
                parseFloat($(this).closest("tr").find(".additional_rate").val())
            ).toFixed(2);
        }
        var item_qty = $(this).closest("tr").find(".item_qty").val();
        var amount = item_price * item_qty;
        $(this).closest("tr").find(".amount").val(amount);
        net_total += amount;
    });
    var dicount = 0;

    if ($("#gross_amount").length) {
        $("#gross_amount").val(net_total);
    }
    if ($("#discount_amount").length) {
        dicount = $("#discount_amount").val();
        if (dicount > 0) {
            net_total = parseFloat(net_total) - parseFloat(dicount);
        }
    }
    $("#net_total").val(net_total);
}
function calculateNetProfit() {
    var net_profit =
        $("#gross_amount").val() -
        $("#gross_purchase_amount").val() -
        $("#discount_amount").val();
    $("#net_profit").val(net_profit);
}
function calculatePurchaseAmountSum() {
    var net_total = 0;
    $(".item_purchase_price").each(function (index) {
        var item_purchase_price = $(this)
            .closest("tr")
            .find(".item_purchase_price")
            .val();
        if ($(this).closest("tr").find(".additional_rate").length) {
            item_purchase_price = (
                parseFloat(item_purchase_price) +
                parseFloat($(this).closest("tr").find(".additional_rate").val())
            ).toFixed(2);
        }
        var item_qty = $(this).closest("tr").find(".item_qty").val();
        var amount = item_purchase_price * item_qty;
        $(this).closest("tr").find(".total_purchase_amount").val(amount);
        net_total += amount;
    });

    if ($("#gross_purchase_amount").length) {
        $("#gross_purchase_amount").val(net_total);
    }
}
function calculateBalanceAmount() {
    var balance_amount = $("#net_total").val() - $("#recieved_amount").val();
    $("#balance_amount").val(balance_amount);
}
function deductEmployeeAmount() {
    var net_total = 0;
    var actual_production_amount = $("#actual_production_amount").text();
    var additional_amount = $("#additional_amount").val();
    var amount_type = $("input[name='amount_type']:checked").val();
    var gross_total = actual_production_amount * amount_type;
    $("#gross_total").val(gross_total);
    var deduction_amount = $("#deduction_amount").val();
    if (gross_total >= deduction_amount) {
        net_total = gross_total - deduction_amount;
    }
    net_total = net_total + Number(additional_amount);
    $("#net_total").val(net_total);
}

$(".bank_check_toggle").click(function (e) {
    $("#show_hide_inps").show();
    $("#account_number_id").hide();
    $("#check_number_id").show();
});
$(".online_check_toggle").click(function (e) {
    $("#show_hide_inps").show();
    $("#check_number_id").hide();
    $("#account_number_id").show();
});
$(".cash").click(function () {
    let first = document.querySelectorAll(".check_number");
    let second = document.querySelectorAll(".bank_name");
    first.forEach((element, index) => {
        first[index].value = "";
        second[index].value = "";
    });

    $(".tr-content:gt(0)").remove();
    $("#show_hide_inps").hide();
});

function calculateLedgerSum() {
    var net_total = 0;
    $(".amountReceipt").each(function (index) {
        var amountReceipt = $(this).val();
        net_total += +amountReceipt;
    });
    $("#net_total").val(net_total);
}

function CalculateEmployeeSalary(employeeid) {
    var total_present = 0;
    var total_absent = 0;
    var total_leave = 0;
    var total_half_days = 0;
    var total_holidays = 0;
    $(".attendece_" + employeeid).each(function (index) {
        var attendeceString = $(this).val();
        var attendeceSplitedArray = attendeceString.split("~");
        var attendece = attendeceSplitedArray[2];
        if (attendece == 1) {
            total_present += 1;
        }
        if (attendece == 2) {
            total_absent += 1;
        }
        if (attendece == 3) {
            total_leave += 1;
        }
        if (attendece == 4) {
            total_half_days += 1;
        }
        if (attendece == 5) {
            total_holidays += 1;
        }
    });

    $("#total_present_" + employeeid).val(total_present);
    $("#total_absent_" + employeeid).val(total_absent);
    $("#total_leave_" + employeeid).val(total_leave);
    $("#total_half_days_" + employeeid).val(total_half_days);
    $("#total_holidays_" + employeeid).val(total_holidays);
    var basic_salary = $("#total_basic_salary_" + employeeid).val();
    var total_working_days =
        Number(total_present) + Number(total_leave) + Number(total_holidays);
    //total_working_days = total_working_days - total_absent;
    total_working_days = (
        parseFloat(total_working_days) + parseFloat(total_half_days / 2)
    ).toFixed(2);
    $("#total_working_days_" + employeeid).val(total_working_days);
    var gross_salary = parseFloat(basic_salary * total_working_days).toFixed(2);
    $("#gross_salary_" + employeeid).val(gross_salary);
    var total_deduction = $("#total_deduction_" + employeeid).val();
    var net_salary = parseFloat(gross_salary - total_deduction).toFixed(2);
    $("#net_salary_" + employeeid).val(net_salary);
}

function removeRow($this) {
    $($this).closest("tr").remove();
}

function deleteRecord(route) {
    $.ajax({
        type: "GET",
        url: route,
        data: {}, // serializes the form's elements.
        success: function (response) {
            if (response.success == true) {
                offloader();
                toasterTitle = "Well done!";
                toasterMessage = response.message;
                successToaster();
                redirectUrl(response.redirectUrl);
            } else {
                offloader();
                toasterTitle = "Error !";
                toasterMessage = response.message;
                errorToaster();
            }
        },
        error: function (response) {
            var response = JSON.parse(response.responseText);
            offloader();
            $.each(response.errors, function (key, value) {
                toasterMessage = value;
            });
            toasterTitle = "Error !";
            errorToaster();
        },
    });
}
//Employee Module Js
function getItemEmployeeRates(itemid) {
    onloader();
    $.ajax({
        type: "GET",
        url: "/getItemEmployeeRates/" + itemid + "",
        headers: {
            "X-CSRF-Token": "{{ csrf_token() }}",
        },
        data: {}, // serializes the form's elements.
        success: function (response) {
            $("#production_table tbody").empty();
            if (response.success == true) {
                var i = 1;
                $.each(response.data, function (key, value) {
                    var amount = (
                        Number($("#production_qty").val()) *
                        parseFloat(value.additional_rate)
                    ).toFixed(2);
                    var item_price = (
                        parseFloat(value.rate) +
                        parseFloat(value.additional_rate)
                    ).toFixed(2);
                    var html = "";
                    html += "<tr>";
                    html += '<td class="text-center">';
                    html += i;
                    html += "</td>";
                    html += '<td class="text-center text-muted">';
                    html +=
                        '<input name="employee_name[]" id="employee_name" placeholder="Employee Name" value="' +
                        value.employee_name +
                        '" type="text" class="form-control">';
                    html +=
                        '<input name="employee_id[]" id="employee_id" placeholder="employee_id" value="' +
                        value.employee_id +
                        '" type="hidden" class="form-control">';
                    html += "</td>";
                    html +=
                        '<td class="text-center"><input name="rate[]" id="rate" placeholder="Rate" value="' +
                        value.rate +
                        '" type="text" class="form-control rate" readonly></td>';
                    html +=
                        '<td class="text-center"><input name="additional_rate[]" id="additional_rate" placeholder="additional rate" value="' +
                        value.additional_rate +
                        '" type="text" class="form-control additional_rate" readonly></td>';

                    html +=
                        '<td class="text-center"><input name="item_price[]" id="item_price" placeholder="Rate" value="' +
                        item_price +
                        '" type="text" class="form-control item_price" readonly></td>';
                    html +=
                        '<td class="text-center"><input name="item_qty[]" id="item_qty" placeholder="Quantity" value="' +
                        $("#production_qty").val() +
                        '" type="text" class="form-control item_qty" readonly></td>';
                    html +=
                        '<td class="text-center"><input name="amount[]" id="amount" placeholder="Total Amount" value="' +
                        amount +
                        '" type="text" class="form-control amount" readonly></td>';
                    html += "</tr>";
                    if ($("#single_employee_id").val() == "") {
                        if (value.production_method == 0) {
                            $("#production_table tbody").append(html);
                        }
                    } else {
                        if (
                            value.employee_id == $("#single_employee_id").val()
                        ) {
                            $("#production_table tbody").append(html);
                        }
                    }

                    i++;
                });
                calculateInvoiceSum();
                offloader();
                //toasterTitle = 'Well done!';
                //toasterMessage = response.message;
                //successToaster();
                //redirectUrl(response.redirectUrl);
            } else {
                offloader();
                //toasterTitle = 'Error !';
                //toasterMessage = response.message;
                //errorToaster();
            }
        },
        error: function (response) {
            var response = JSON.parse(response.responseText);
            offloader();
            $.each(response.errors, function (key, value) {
                toasterMessage = value;
            });
            toasterTitle = "Error !";
            errorToaster();
        },
    });
}

function updateProductionQty() {
    $(".item_qty").val($("#production_qty").val());
    calculateInvoiceSum();
}

function openGoogleMaps() {
    // Replace with the location address you want to display
    const locationAddress = $("#map_location").text().trim();
    // Encode the address for use in the Google Maps URL
    const encodedAddress = encodeURIComponent(locationAddress);

    // Construct the Google Maps URL
    const mapsUrl = `https://www.google.com/maps?q=${encodedAddress}`;

    // Open the link in a new tab
    window.open(mapsUrl, "_blank");
}

async function GetItemByCode() {
    var code = $("#code").val();
    $("#code").val(""); //Prevent double Enter press issue
    if (code == "") {
        return false;
    }
    let url = `${window.location.origin}/item/data`;
    $.ajax({
        type: "POST",
        url: url,
        data: { code: code },
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success: await function (data) {
            if (data.data != null) {
                var item = data.data;
                var finditem = false;
                $(".itemData").each(function (index) {
                    if ($(this).val() == item.id) {
                        // $(this)
                        //     .closest("tr")
                        //     .find(".item_qty")
                        //     .val(
                        //         Number(
                        //             $(this)
                        //                 .closest("tr")
                        //                 .find(".item_qty")
                        //                 .val()
                        //         ) + Number(1)
                        //     );
                        finditem = true;
                    }
                });
                if (finditem == false) {
                    $(".add_row").trigger("click");
                    // $(".item_row:last").find(".item_qty").val(1);
                    // $('.item_row:last').find('.sale_price').val(item.sele_price);
                    $(".item_row:last").find(".itemData").val(item.id).change();
                }
                calculateInvoiceSum();
                calculateNetProfit();
                calculatePurchaseAmountSum();
            }
            $("#code").val("");
            $("#code").focus();
        },
        error: function (err) {
            console.log(err);
        },
    });
}

$(".Q-form").on("keypress", function (e) {
    var keyCode = e.keyCode || e.which;
    var elementId = e.target.id;
    if (keyCode == 13) {
        //if user press Enter then check
        if (elementId == "code") {
            GetItemByCode();
        }
        e.preventDefault();
        return false;
    }
});
$("#code").on("change", function (e) {
    GetItemByCode();
});

$("#save_button").click(function (e) {
    $("#save_button").hide();
});

$(document).ready(function () {
    $(".js-example-basic-single").select2();
    $(".add_row").click(function () {
        var e = $(".new_row").find(".sr_no");
        var sr_no = Number(e.text()) + Number(1);
        e.text(sr_no);
        $($(".new_row").html()).insertBefore($(".btn-add-new"));
        $(".select-drop-down").last().select2();
        $(".new_row").find(".sr_no").text(sr_no);
    });

    $("body").on("click", ".removeRow", function () {
        $(this).closest("tr").remove();
    });

    // $('#show_hide_inps').hide();

    $(".Q-form").submit(function (e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr("action");
        var formData = new FormData(this);
        onloader();
        $.ajax({
            type: "POST",
            url: url,
            data: formData, // serializes the form's elements.
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response.success == true) {
                    offloader();
                    toasterTitle = "Well done!";
                    toasterMessage = response.message;
                    successToaster();
                    if (response.print) {
                        window.open(response.print, "_blank");
                    }
                    if (response.redirectUrl == "currentPage") {
                        location.reload();
                    } else {
                        redirectUrl(response.redirectUrl);
                    }
                    $("#save_button").show();
                } else {
                    offloader();
                    toasterTitle = "Error !";
                    toasterMessage = response.message;
                    errorToaster();
                    $("#save_button").show();
                }
            },
            error: function (response) {
                var response = JSON.parse(response.responseText);
                offloader();
                $.each(response.errors, function (key, value) {
                    toasterMessage = value;
                });
                toasterTitle = "Error !";
                errorToaster();
                $("#save_button").show();
            },
        });
    });
});
