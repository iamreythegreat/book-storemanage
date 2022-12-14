$(document)
    .on("click", "#iw-alert .iwAlertBackdrop", iwBackdropClicked)
    .on("click", "#iw-alert .iwAlertBox", (event) => preventEvent(event))
    .on("click", "#iw-alert .iwAlertOk", iwOkClicked)
    .on("click", "#iw-alert .iwAlertYes", iwYesClicked)
    .on("click", "#iw-alert .iwAlertNo", iwNoClicked)

    .on("click", ".iwTimePicker", (event) => preventEvent(event))
    .on("click", ".iwTimePicker .iwTimePickerHour", (event) => iwTimePicker_ActivateSelf(event))
    .on("click", ".iwTimePicker .iwTimePickerMinute", (event) => iwTimePicker_ActivateSelf(event))
    .on("click", ".iwTimePicker .iwTimePickerMeridian", (event) => iwTimePicker_ActivateSelf(event))
    .on("click", ".iwTimePicker .iwTimePicker-accept", (event) => iwTimePicker_Accept(event))
    .on("click", ".iwTimePicker .iwTimePicker-clear", (event) => iwTimePicker_Clear(event))
    .on("click", ".iwTimePicker .iwTimePicker-cancel", (event) => iwTimePicker_Cancel(event))

    .on("keyup", event => {
        if (!$("#iw-alert").is(":visible")) {
            return;
        }

        if (event.keyCode === 13) {
            iwYesClicked();
        }

        if (event.keyCode === 27) {
            iwBackdropClicked();
        }
    })

    .on("click", "#iw-alert .iwAlertPrompt-time .iwPromptInputValue", event => {
        initIWTimePicker($("#iw-alert .iwAlertPrompt-time .iwPromptInput").val());
        $(".iwTimePicker").addClass("active");
    })
    .on("iwalert.time.selected", (event, response) => {
        if (!response.success) {
            return;
        }

        if (response.data === null) {
            $("#iw-alert .iwAlertPrompt-time .iwPromptInputValue").text(null);
        } else {
            $("#iw-alert .iwAlertPrompt-time .iwPromptInputValue").text(moment(response.data).format("h:mma"));
        }

        $("#iw-alert .iwAlertPrompt-time .iwPromptInput").val(response.data);
    })

function preventEvent(event) {
    event.preventDefault();
    event.stopPropagation();
    event.stopImmediatePropagation();
}

function iwAlert(content, hideOnDismiss) {
    initIwAlert();

    let $alert = $('#iw-alert');

    $alert
        .find(".iwActions_alert")
        .show();

    $alert
        .find(".iwActions_confirm")
        .hide();

    $alert
        .find(".iwAlertContent")
        .html(content);

    $alert
        .find(".iwAlertPromptWrap")
        .hide();

    $alert.fadeIn();

    return new Promise((resolve, reject) => {
        const ok_handler = (event, resp) => {
            $(document).off("iwalert.dismiss", dismiss_handler);

            iwAlertDismiss(hideOnDismiss);
            resolve();
        };

        const dismiss_handler = (event, resp) => {
            $(document).off("iwalert.ok", ok_handler);

            iwAlertDismiss(hideOnDismiss);
            reject();
        };

        $(document)
            .one("iwalert.ok", ok_handler)
            .one("iwalert.dismiss", dismiss_handler);
    });
}

function iwConfirm(content, options, hideOnDismiss) {
    let defaultOptions = {
        buttons: {
            yes: { label: "Yes", classes: [] },
            no: { label: "No", classes: [] },
        }
    };

    options = $.extend(true, defaultOptions, options === undefined ? {} : options);

    initIwAlert(options);

    let $alert = $('#iw-alert');

    $alert
        .find(".iwActions_alert")
        .hide();

    $alert
        .find(".iwActions_confirm")
        .show();

    $alert
        .find(".iwAlertContent")
        .html(content);

    $alert
        .find(".iwAlertPromptWrap")
        .hide();

    $alert.fadeIn();

    return new Promise((resolve, reject) => {
        const yes_handler = (event, resp) => {
            $(document)
                .off("iwalert.no", no_handler)
                .off("iwalert.dismiss", dismiss_handler);

            iwAlertDismiss(hideOnDismiss);
            resolve();
        };

        const no_handler = (event, resp) => {
            $(document)
                .off("iwalert.yes", yes_handler)
                .off("iwalert.dismiss", dismiss_handler);

            iwAlertDismiss(hideOnDismiss);
            reject();
        };

        const dismiss_handler = (event, resp) => {
            $(document)
                .off("iwalert.yes", yes_handler)
                .off("iwalert.no", no_handler);

            iwAlertDismiss(hideOnDismiss);
            reject();
        };

        $(document)
            .one("iwalert.yes", yes_handler)
            .one("iwalert.no", no_handler)
            .one("iwalert.dismiss", dismiss_handler);
    });
}

function iwPrompt(content, options, hideOnDismiss) {
    let defaultOptions = {
        input: {
            value: "",
            rows: 1,
            type: "text",
        },
        buttons: {
            yes: { label: "Yes", classes: [] },
            no: { label: "No", classes: [] },
        },
        content: { classes: [] }
    };

    options = $.extend(true, defaultOptions, options === undefined ? {} : options);

    options.content.classes.push("iwAlertContent");

    initIwAlert(options);

    let $alert = $('#iw-alert');

    $alert
        .find(".iwActions_alert")
        .hide();

    $alert
        .find(".iwActions_confirm")
        .show();

    $alert
        .find(".iwAlertPromptWrap")
        .show();

    $alert
        .find(".iwAlertContent")
        .removeClass()
        .addClass(options.content.classes.join(" "))
        .html(content);

    $alert
        .find(".iwPromptInput.active")
        .removeClass("active");

    switch (options.input.type) {
        case "time":
            initIwPrompt_Time(content, options);
            break;
            
        case "input":
            initIwPrompt_Input(content, options);
            break;
            
        case "select":
            initIwPrompt_Select(content, options);
            break;

        case "text":
        default:
            initIwPrompt_Text(content, options);
            break;
    }

    $alert.fadeIn();

    return new Promise((resolve, reject) => {
        const yes_handler = (event, resp) => {
            $(document)
                .off("iwalert.no", no_handler)
                .off("iwalert.dismiss", dismiss_handler);

            iwAlertDismiss(hideOnDismiss);
            resolve($alert.find(".iwPromptInput.active").val());
        };

        const no_handler = (event, resp) => {
            $(document)
                .off("iwalert.yes", yes_handler)
                .off("iwalert.dismiss", dismiss_handler);

            iwAlertDismiss(hideOnDismiss);
            reject();
        };

        const dismiss_handler = (event, resp) => {
            $(document)
                .off("iwalert.yes", yes_handler)
                .off("iwalert.no", no_handler);

            iwAlertDismiss(hideOnDismiss);
            reject();
        };

        $(document)
            .one("iwalert.yes", yes_handler)
            .one("iwalert.no", no_handler)
            .one("iwalert.dismiss", dismiss_handler);
    });
}

function initIwPrompt_Input(content, options) {
    const $alert = $('#iw-alert');

    $alert
        .find(".iwAlertPromptWrap")
        .children()
        .hide();

    const $prompt_text = $alert.find(".iwAlertPrompt-input");

    $prompt_text
        .show()
        .find(".iwPromptInput")
        .attr("rows", options.input.rows)
        .addClass("active")
        .val(options.input.value);

    $prompt_text.show();
}

function initIwPrompt_Select(content, options) {
    const $alert = $('#iw-alert');

    options = $.extend(true, { input: { options: [] } }, options);

    $alert
        .find(".iwAlertPromptWrap")
        .children()
        .hide();

    const $prompt_text = $alert.find(".iwAlertPrompt-select");

    $prompt_text
        .show()
        .find(".iwPromptInput")
        .html(options.input.options.join(""))
        .addClass("active")
        .val(options.input.value);

    $prompt_text.show();
}

function initIwPrompt_Text(content, options) {
    const $alert = $('#iw-alert');

    $alert
        .find(".iwAlertPromptWrap")
        .children()
        .hide();

    const $prompt_text = $alert.find(".iwAlertPrompt-text");

    $prompt_text
        .show()
        .find(".iwPromptInput")
        .attr("rows", options.input.rows)
        .addClass("active")
        .val(options.input.value);

    $prompt_text.show();
}

function initIwPrompt_Time(content, options) {
    const $alert = $('#iw-alert');

    $alert
        .find(".iwAlertPromptWrap")
        .children()
        .hide();

    const $prompt_time = $alert.find(".iwAlertPrompt-time");

    $prompt_time
        .show()
        .find(".iwPromptInput")
        .addClass("active")
        .val(options.input.value);

    let label_value = "&nbsp;";

    if (options.input.value !== undefined && options.input.value !== null) {
        label_value = moment(options.input.value).format("h:mma");
    }

    $prompt_time
        .find(".iwPromptInputValue")
        .html(label_value);

    $prompt_time.show();
}

function iwLoader(content) {
    if (content === undefined) { content = ""; }
    initIwAlert();

    let $alert = $('#iw-alert');

    $alert
        .find(".iwActions_alert")
        .hide();

    $alert
        .find(".iwActions_confirm")
        .hide();

    $alert
        .find(".iwAlertContent")
        .html(`<div class="iwAlertLoaderWrap"><div class="iwAlertLoaderContent">${content}</div><div class="iwAlertLoader"><span></span><span></span></div></div>`);

    $alert
        .find(".iwAlertPromptWrap")
        .hide();

    $alert.fadeIn();
}

function initIwAlert(options) {
    let defaultOptions = {
        input: {
            value: "",
            rows: 1,
        },
        buttons: {
            yes: {
                label: "Yes",
                classes: [],
            },
            no: {
                label: "No",
                classes: [],
            },
        }
    };

    if (options === undefined) {
        options = defaultOptions;
    }

    let $alert = $('#iw-alert');
    if ($alert.length === 0) {
        $("body").append(renderIwAlert());
        $alert = $('#iw-alert');
    }

    let yes_classes = ["btn iwAlertBtn iwAlertYes", ...options.buttons.yes.classes];
    let no_classes  = ["btn iwAlertBtn iwAlertNo", ...options.buttons.no.classes];

    $alert
        .find(".iwAlertYes")
        .addClass(yes_classes.join(" "))
        .text(options.buttons.yes.label ? options.buttons.yes.label : "");

    $alert
        .find(".iwAlertNo")
        .addClass(no_classes.join(" "))
        .text(options.buttons.no.label ? options.buttons.no.label : "");

    if (options.buttons.no.show === false) {
        $alert
            .find(".iwAlertNo")
            .hide();
    }
}

function iwAlertDismiss(hideOnDismiss, fade = true) {
    $('.iwTimePicker').removeClass("active");

    if (hideOnDismiss === false) {
        return;
    }
    
    if(fade){
        $('#iw-alert').fadeOut();
        return;
    }

    $('#iw-alert').hide();
}

function renderIwAlert() {
    return `<div id="iw-alert" class="iwAlertContainer">
                <div class="iwAlertBackdrop">
                    <div class="iwAlertBox">
                        <div class="iwAlertBody">
                            <div class="iwAlertContent"></div>
                            <div class="iwAlertPromptWrap">
                                <div class="iwAlertPrompt-text">
                                    <textarea class="iwPromptInput"></textarea>
                                </div>
                                <div class="iwAlertPrompt-input">
                                    <input class="iwPromptInput"/>
                                </div>
                                <div class="iwAlertPrompt-select">
                                    <select class="iwPromptInput"></select>
                                </div>
                                <div class="iwAlertPrompt-time">
                                    <div class="iwPromptInputValue disabled text-center">&nbsp;</div>
                                    <input type="hidden" class="timepicker iwPromptInput" />
                                </div>
                            </div>
                        </div>
                        <div class="iwAlertActions">
                            <div class="iwActions_alert">
                                <div class="btn iwAlertBtn iwAlertOk">OK</div>
                            </div>
                            <div class="iwActions_confirm">
                                <div class="btn iwAlertBtn iwAlertNo">No</div>
                                <div class="btn iwAlertBtn iwAlertYes">Yes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            ${getIWTimePickerHtml()}`;
}

function getIWTimePickerHtml() {
    return `<div class="iwTimePicker">
                <div class="iwTimePickerTitles">
                    <div class="iwTimePickerTitle">Hours</div>
                    <div class="iwTimePickerTitle">Minutes</div>
                    <div class="iwTimePickerTitle">&nbsp;</div>
                </div>
                <div class="iwTimePickerHours">
                    <div class="iwTimePickerHour" data-value="1">1</div>
                    <div class="iwTimePickerHour" data-value="2">2</div>
                    <div class="iwTimePickerHour" data-value="3">3</div>
                    <div class="iwTimePickerHour" data-value="4">4</div>
                    <div class="iwTimePickerHour" data-value="5">5</div>
                    <div class="iwTimePickerHour" data-value="6">6</div>
                    <div class="iwTimePickerHour" data-value="7">7</div>
                    <div class="iwTimePickerHour" data-value="8">8</div>
                    <div class="iwTimePickerHour" data-value="9">9</div>
                    <div class="iwTimePickerHour" data-value="10">10</div>
                    <div class="iwTimePickerHour" data-value="11">11</div>
                    <div class="iwTimePickerHour" data-value="12">12</div>
                </div>
                <div class="iwTimePickerMinutes">
                    <div class="iwTimePickerMinute interval-10" data-value="0">00</div>
                    <div class="iwTimePickerMinute " data-value="5">05</div>
                    <div class="iwTimePickerMinute interval-10" data-value="10">10</div>
                    <div class="iwTimePickerMinute " data-value="15">15</div>
                    <div class="iwTimePickerMinute interval-10" data-value="20">20</div>
                    <div class="iwTimePickerMinute " data-value="25">25</div>
                    <div class="iwTimePickerMinute interval-10" data-value="30">30</div>
                    <div class="iwTimePickerMinute " data-value="35">35</div>
                    <div class="iwTimePickerMinute interval-10" data-value="40">40</div>
                    <div class="iwTimePickerMinute " data-value="45">45</div>
                    <div class="iwTimePickerMinute interval-10" data-value="50">50</div>
                    <div class="iwTimePickerMinute " data-value="55">55</div>
                </div>
                <div class="iwTimePickerMeridians">
                    <div class="iwTimePickerMeridian" data-value="am">AM</div>
                    <div class="iwTimePickerMeridian" data-value="pm">PM</div>
                    <div class="iwTimePickerActions">
                        <div class="iwTimePickerAction iwTimePicker-accept btn btn-success d-block text-center">ACCEPT</div>
                        <div class="iwTimePickerAction iwTimePicker-clear btn btn-danger d-block text-center">CLEAR</div>
                        <div class="iwTimePickerAction iwTimePicker-cancel btn d-block text-center">CANCEL</div>
                    </div>
                </div>
            </div>`;
}

function getIWDatePickerHtml() {
    //WIP
    return `<div class="iwDatePicker">
                <div class="iwDatePickerHead">
                    <div class="iwDatePickerPrev"></div>
                    <div class="iwDatePickerMonth"></div>
                    <div class="iwDatePickerNext"></div>
                </div>
                <div class="iwDatePickerBody">
                </div>
                <div class="iwDatePickerFoot">
                    <div class="iwDatePickerActions">
                        <div class="iwDatePickerAction iwDatePicker-accept btn btn-success d-block text-center">ACCEPT</div>
                        <div class="iwDatePickerAction iwDatePicker-cancel btn d-block text-center">CANCEL</div>
                    </div>
                </div>
            </div>`;
}

function iwBackdropClicked() {
    $(document).trigger("iwalert.dismiss");
}

function iwOkClicked() {
    $(document).trigger("iwalert.ok");
}

function iwYesClicked() {
    $(document).trigger("iwalert.yes");
}

function iwNoClicked() {
    $(document).trigger("iwalert.no");
}


function iwTimePicker_ActivateSelf(event) {
    $(event.currentTarget)
        .addClass("active")
        .siblings()
        .removeClass("active")
}

function iwTimePicker_Accept(event) {
    $(document).trigger("iwalert.time.selected", [{ success: true, data: iwTimePicker_GetValue() }]);
    $(".iwTimePicker").removeClass("active");
}

function iwTimePicker_Clear(event) {
    $(document).trigger("iwalert.time.selected", [{ success: true, data: null }]);
    $(".iwTimePicker").removeClass("active");
}

function iwTimePicker_Cancel(event) {
    $(document).trigger("iwalert.time.selected", [{ success: false }]);
    $(".iwTimePicker").removeClass("active");
}

function iwTimePicker_GetValue() {
    const input_value = $("#iw-alert .iwAlertPrompt-time .iwPromptInput").val().replace("Invalid Date", "");
    const current_date = moment(input_value === "" ? undefined : input_value).format("YYYY-MM-DD");
    const minute = parseInt($(".iwTimePicker .iwTimePickerMinute.active").attr("data-value") ?? 0);
    const meridian = $(".iwTimePicker .iwTimePickerMeridian.active").attr("data-value") ?? "am";
    let hour = parseInt($(".iwTimePicker .iwTimePickerHour.active").attr("data-value") ?? 1);

    if (meridian === "pm" && hour !== 12) {
        hour = hour + 12;
    }

    if (meridian === "am" && hour === 12) {
        hour = 0;
    }

    return `${current_date} ${pad(hour)}:${pad(minute)}:00`;
}

function initIWTimePicker(time_value, interval) {
    if(interval === undefined){
        interval = 5;
    }

    const $time_picker = $(".iwTimePicker");
    const $hours = $time_picker.find(".iwTimePickerHours").children();
    const $minutes = $time_picker.find(".iwTimePickerMinutes").children();
    const $meridians = $time_picker.find(".iwTimePickerMeridians").children();

    $hours.removeClass("active");
    $minutes.removeClass("active");
    $meridians.removeClass("active");

    if (time_value === null || time_value === "") {
        time_value = undefined;
    }

    if(time_value.indexOf(":") !== -1 && time_value.indexOf(" ") === -1){
        time_value = moment().format("YYYY-MM-DD ") + time_value;
    }

    const current_time = moment(time_value);
    const current_hour = parseInt(current_time.format("h"));
    const current_minute = Math.floor(parseInt(current_time.format("m")) / interval) * interval;
    const current_meridian = current_time.format("a");

    $hours
        .filter(`[data-value="${current_hour}"]`)
        .addClass("active");

    $minutes
        .filter(`[data-value="${current_minute}"]`)
        .addClass("active");

    $meridians
        .filter(`[data-value="${current_meridian}"]`)
        .addClass("active");
}

function getClosest5Min(time) {
    time = moment(time);

    const current_minute = Math.floor(parseInt(time.format("m")) / 5) * 5;

    return `${time.format("YYYY-MM-DD HH")}:${pad(current_minute)}:00`;
}

function getClosest10Min(time) {
    time = moment(time);

    const current_minute = Math.floor(parseInt(time.format("m")) / 10) * 10;

    return `${time.format("YYYY-MM-DD HH")}:${pad(current_minute)}:00`;
}

function pad(input) {
    return input < 10 ? `0${input}` : input;
}

function iwToast(content, classes, time_to_display) {
    initIwToast();

    const $wrap = $("#iw-toast-wrap")
    const $toast = $(`<div class="iw-toast ${classes}">${content}</div>`);

    $wrap.append($toast)
    $toast
        .css("display", "flex")
        .hide()
        .fadeIn()

    setTimeout(() => {
        $toast.fadeOut()

        setTimeout(() => $toast.remove(), 500)
    }, (time_to_display ?? 2000) + 500);
}

function initIwToast() {
    if ($("#iw-toast-wrap").length > 0) {
        return;
    }

    $("body").append(`<div class="iw-toast-wrap" id="iw-toast-wrap"></div>`)
}
