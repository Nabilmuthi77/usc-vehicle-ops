export function initDatePicker() {
    const pickers = document.querySelectorAll("[data-date-picker]");

    if (pickers.length === 0) return;

    pickers.forEach((picker) => {


    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const button = picker.querySelector("[data-date-button]");
    const dropdown = picker.querySelector("[data-date-dropdown]");

    const hiddenInput = picker.querySelector("[data-date-value]");
    const label = picker.querySelector("[data-date-label]");
    const arrow = picker.querySelector("[data-date-arrow]");

    const prevButton = picker.querySelector("[data-date-prev]");
    const nextButton = picker.querySelector("[data-date-next]");

    const monthButton = picker.querySelector("[data-date-month-button]");
    const yearButton = picker.querySelector("[data-date-year-button]");

    const monthLabel = picker.querySelector("[data-date-month]");
    const yearLabel = picker.querySelector("[data-date-year]");

    const calendar = picker.querySelector("[data-date-calendar]");
    const daysContainer = picker.querySelector("[data-date-days]");

    const yearPicker = picker.querySelector("[data-year-picker]");
    const yearGrid = picker.querySelector("[data-year-grid]");

    const monthPicker = picker.querySelector("[data-month-picker]");
    const monthOptions = picker.querySelectorAll("[data-month-option]");

    const clearButton = picker.querySelector("[data-date-clear]");


    /*
    |--------------------------------------------------------------------------
    | Month Names
    |--------------------------------------------------------------------------
    */

    const monthNames = [
        "Januari",
        "Februari",
        "Maret",
        "April",
        "Mei",
        "Juni",
        "Juli",
        "Agustus",
        "September",
        "Oktober",
        "November",
        "Desember",
    ];


    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    let selectedDate = hiddenInput.value
        ? new Date(`${hiddenInput.value}T00:00:00`)
        : null;

    const today = new Date();

    let currentMonth = selectedDate
        ? selectedDate.getMonth()
        : today.getMonth();

    let currentYear = selectedDate
        ? selectedDate.getFullYear()
        : today.getFullYear();


    /*
    |--------------------------------------------------------------------------
    | Format
    |--------------------------------------------------------------------------
    */

    function formatDate(date) {
        return `${date.getFullYear()}-${String(
            date.getMonth() + 1
        ).padStart(2, "0")}-${String(
            date.getDate()
        ).padStart(2, "0")}`;
    }


    function formatLabel(date) {
        return `${date.getDate()} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
    }


    /*
    |--------------------------------------------------------------------------
    | Label
    |--------------------------------------------------------------------------
    */

    function updateLabel() {
        if (selectedDate) {
            label.textContent = formatLabel(selectedDate);

            label.classList.remove("text-gray-400", "font-normal", "text-sm");
            label.classList.add("text-[#02081C]", "font-medium", "text-sm");
        } else {
            label.textContent = button.dataset.placeholder || "Pilih tanggal";

            label.classList.remove("text-[#02081C]");
            label.classList.add("text-gray-400", "font-normal", "text-sm");
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Header
    |--------------------------------------------------------------------------
    */

    function updateHeader() {
        monthLabel.textContent = monthNames[currentMonth];
        yearLabel.textContent = currentYear;
    }


    /*
    |--------------------------------------------------------------------------
    | View Manager
    |--------------------------------------------------------------------------
    */

    function showCalendar() {
        calendar.classList.remove("hidden");

        yearPicker.classList.add("hidden");
        monthPicker.classList.add("hidden");

        updateHeader();
        renderCalendar();
    }


    function showYears() {
        calendar.classList.add("hidden");

        monthPicker.classList.add("hidden");
        yearPicker.classList.remove("hidden");

        renderYears();
    }


    function showMonths() {
        calendar.classList.add("hidden");

        yearPicker.classList.add("hidden");
        monthPicker.classList.remove("hidden");

        renderMonths();
    }


    /*
    |--------------------------------------------------------------------------
    | Year Picker
    |--------------------------------------------------------------------------
    */

    function renderYears() {
        yearGrid.innerHTML = "";

        const startYear = currentYear - 6;
        const endYear = currentYear + 5;

        for (let year = startYear; year <= endYear; year++) {
            const yearOption = document.createElement("button");

            yearOption.type = "button";
            yearOption.textContent = year;

            yearOption.className =
                "rounded-xl px-2 py-2.5 text-xs font-semibold transition";

            if (year === currentYear) {
                yearOption.classList.add(
                    "bg-usc-500",
                    "text-white",
                    "shadow-sm"
                );
            } else {
                yearOption.classList.add(
                    "text-slate-600",
                    "hover:bg-usc-50",
                    "hover:text-usc-700"
                );
            }


            /*
            |--------------------------------------------------------------
            | Pilih Tahun
            |--------------------------------------------------------------
            */

            yearOption.addEventListener("click", (event) => {
                event.stopPropagation();

                currentYear = year;

                /*
                 * Balik ke kalender.
                 * Tidak membuka month picker.
                 */

                showCalendar();
            });


            yearGrid.appendChild(yearOption);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Month Picker
    |--------------------------------------------------------------------------
    */

    function renderMonths() {
        monthOptions.forEach((option) => {
            const month =
                Number(option.dataset.monthOption) - 1;

            option.classList.remove(
                "bg-usc-500",
                "text-white",
                "shadow-sm",
                "text-slate-600"
            );

            if (month === currentMonth) {
                option.classList.add(
                    "bg-usc-500",
                    "text-white",
                    "shadow-sm"
                );
            } else {
                option.classList.add(
                    "text-slate-600"
                );
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Render Calendar
    |--------------------------------------------------------------------------
    */

    function renderCalendar() {
        daysContainer.innerHTML = "";


        const firstDay = new Date(
            currentYear,
            currentMonth,
            1
        ).getDay();


        const daysInMonth = new Date(
            currentYear,
            currentMonth + 1,
            0
        ).getDate();


        const previousMonthDays = new Date(
            currentYear,
            currentMonth,
            0
        ).getDate();


        /*
        |--------------------------------------------------------------
        | Previous Month
        |--------------------------------------------------------------
        */

        for (let i = firstDay - 1; i >= 0; i--) {
            const day = previousMonthDays - i;

            daysContainer.appendChild(
                createDayButton(
                    day,
                    currentYear,
                    currentMonth - 1,
                    true
                )
            );
        }


        /*
        |--------------------------------------------------------------
        | Current Month
        |--------------------------------------------------------------
        */

        for (let day = 1; day <= daysInMonth; day++) {
            daysContainer.appendChild(
                createDayButton(
                    day,
                    currentYear,
                    currentMonth,
                    false
                )
            );
        }


        /*
        |--------------------------------------------------------------
        | Next Month
        |--------------------------------------------------------------
        */

        const totalCells =
            firstDay + daysInMonth;

        const remaining =
            totalCells % 7 === 0
                ? 0
                : 7 - (totalCells % 7);


        for (let day = 1; day <= remaining; day++) {
            daysContainer.appendChild(
                createDayButton(
                    day,
                    currentYear,
                    currentMonth + 1,
                    true
                )
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Day Button
    |--------------------------------------------------------------------------
    */

    function createDayButton(
        day,
        year,
        month,
        muted = false
    ) {
        const date = new Date(
            year,
            month,
            day
        );

        const dayButton =
            document.createElement("button");

        dayButton.type = "button";
        dayButton.textContent = day;

        dayButton.className =
            "flex h-8 w-8 items-center justify-center rounded-lg text-xs font-medium transition";


        /*
        |--------------------------------------------------------------
        | Muted Date
        |--------------------------------------------------------------
        */

        if (muted) {
            dayButton.classList.add(
                "text-slate-300",
                "hover:bg-slate-50"
            );
        } else {
            dayButton.classList.add(
                "text-slate-600",
                "hover:bg-usc-50",
                "hover:text-usc-700"
            );
        }


        /*
        |--------------------------------------------------------------
        | Today
        |--------------------------------------------------------------
        */

        if (
            formatDate(date) ===
            formatDate(today)
        ) {
            dayButton.classList.add(
                "font-bold",
                "text-usc-600",
                "ring-1",
                "ring-usc-200"
            );
        }


        /*
        |--------------------------------------------------------------
        | Selected
        |--------------------------------------------------------------
        */

        if (
            selectedDate &&
            formatDate(date) ===
            formatDate(selectedDate)
        ) {
            dayButton.className =
                "flex h-8 w-8 items-center justify-center rounded-lg bg-usc-500 text-xs font-bold text-white shadow-sm";
        }


        /*
        |--------------------------------------------------------------
        | Select Date
        |--------------------------------------------------------------
        */

        dayButton.addEventListener("click", () => {
            selectedDate = date;

            hiddenInput.value =
                formatDate(date);
                
            hiddenInput.dispatchEvent(new Event("change", { bubbles: true }));

            updateLabel();

            closePicker();
        });


        return dayButton;
    }


    /*
    |--------------------------------------------------------------------------
    | Open / Close
    |--------------------------------------------------------------------------
    */

    function openPicker() {
        dropdown.classList.remove("hidden");
        button.setAttribute("aria-expanded", "true");

        arrow?.classList.add("rotate-180");

        dropdown.style.top = 'calc(100% + 8px)';
        dropdown.style.bottom = 'auto';
        
        const rect = dropdown.getBoundingClientRect();
        const btnRect = button.getBoundingClientRect();
        
        if (rect.bottom > window.innerHeight && btnRect.top > rect.height) {
            dropdown.style.top = 'auto';
            dropdown.style.bottom = 'calc(100% + 8px)';
        }

        showCalendar();
    }


    function closePicker() {
        dropdown.classList.add("hidden");
        button.setAttribute("aria-expanded", "false");

        arrow?.classList.remove("rotate-180");

        showCalendar();
    }


    /*
    |--------------------------------------------------------------------------
    | Main Button
    |--------------------------------------------------------------------------
    */

    button.addEventListener("click", (event) => {
        event.stopPropagation();

        if (
            dropdown.classList.contains("hidden")
        ) {
            openPicker();
        } else {
            closePicker();
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Year Button
    |--------------------------------------------------------------------------
    */

    yearButton.addEventListener("click", (event) => {
        event.stopPropagation();

        showYears();
    });


    /*
    |--------------------------------------------------------------------------
    | Month Button
    |--------------------------------------------------------------------------
    */

    monthButton.addEventListener("click", (event) => {
        event.stopPropagation();

        showMonths();
    });


    /*
    |--------------------------------------------------------------------------
    | Previous Month
    |--------------------------------------------------------------------------
    */

    prevButton.addEventListener("click", (event) => {
        event.stopPropagation();

        currentMonth--;

        if (currentMonth < 0) {
            currentMonth = 11;
            currentYear--;
        }

        showCalendar();
    });


    /*
    |--------------------------------------------------------------------------
    | Next Month
    |--------------------------------------------------------------------------
    */

    nextButton.addEventListener("click", (event) => {
        event.stopPropagation();

        currentMonth++;

        if (currentMonth > 11) {
            currentMonth = 0;
            currentYear++;
        }

        showCalendar();
    });


    /*
    |--------------------------------------------------------------------------
    | Month Options
    |--------------------------------------------------------------------------
    */

    monthOptions.forEach((option) => {
        option.addEventListener("click", (event) => {
            event.stopPropagation();

            currentMonth =
                Number(
                    option.dataset.monthOption
                ) - 1;

            /*
             * Balik ke kalender.
             * Tidak melakukan apa-apa ke year picker.
             */

            showCalendar();
        });
    });


    /*
    |--------------------------------------------------------------------------
    | Clear
    |--------------------------------------------------------------------------
    */

    clearButton?.addEventListener("click", (event) => {
        event.stopPropagation();

        selectedDate = null;

        hiddenInput.value = "";
        
        hiddenInput.dispatchEvent(new Event("change", { bubbles: true }));

        updateLabel();

        closePicker();
    });


    /*
    |--------------------------------------------------------------------------
    | Click Outside
    |--------------------------------------------------------------------------
    */

    document.addEventListener("click", (event) => {
        if (!picker.contains(event.target)) {
            closePicker();
        }
    });


    /*
    |--------------------------------------------------------------------------
    | Init
    |--------------------------------------------------------------------------
    */

    updateLabel();
    updateHeader();
    renderCalendar();
    });
}