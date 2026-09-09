import './bootstrap';
import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;
Alpine.start();

import { initCountryPicker } from './country-picker';
import { initPasswordToggle } from './password-toggle';
import { initPhoneFormat } from './phone-format';
import { initAlert } from './alert';
import { initDatePicker } from './date-picker';
import { initGenderPicker } from './gender-picker';
import { initRolePicker } from './role-picker';
import { initAddressDetail } from './address-detail';
import { initMobileSidebar } from './mobile-sidebar';


document.addEventListener('DOMContentLoaded', function () {

    initPasswordToggle();
    initCountryPicker();
    initPhoneFormat();
    initAlert();
    initDatePicker();
    initGenderPicker();
    initRolePicker();
    initAddressDetail();
    initMobileSidebar();

});