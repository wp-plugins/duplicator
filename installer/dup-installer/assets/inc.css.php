<?php defined("DUPXABSPATH") or die(""); ?>
<style>
/*! ******
HELPER CALSSES
*******/
.float-right {
    float: right;
}
.float-left {
    float: left;
}
.clearfix:before,
.clearfix:after {
    content: " "; /* 1 */
    display: table; /* 2 */
}

.clearfix:after {
    clear: both;
}

.no-display { 
    display: none; 
}

.hidden {
    visibility: hidden;
    opacity: 0;
}

.monospace {
    font-family: monospace;
}

/* COLORS */
.transparent {opacity: 0;}
.red {color: #AF0000;}
.orangered {color: orangered;}
.green {color: #008000;}
.maroon {color:maroon;}
.silver {color:silver;}
.gray {color:gray;}
.white {color:#fff;}

/* FONT-SIZE */
.font-size-11 {font-size: 11px}
.font-size-12 {font-size: 12px}
.font-size-13 {font-size: 13px}
.font-size-14 {font-size: 14px}
.font-size-15 {font-size: 15px}
.font-size-16 {font-size: 16px}
.font-size-17 {font-size: 17px}
.font-size-18 {font-size: 18px}

.text-center {
    text-align: center;
}

.text-right {
    text-align: right;
}

.display-inline {
    display: inline;
}

.display-inline-block {
    display: inline-block;
}

.display-block {
    display: block;
} 

.margin-top-0 {
    margin-top: 0;
}

.margin-top-1 {
    margin-top: 20px;
}

.margin-top-2 {
    margin-top: 40px;
}

.margin-top-3 {
    margin-top: 60px;
}

.margin-top-4 {
    margin-top: 80px;
}

.margin-bottom-0 {
    margin-bottom: 0;
}

.margin-bottom-1 {
    margin-bottom: 20px;
}

.margin-bottom-2 {
    margin-bottom: 40px;
}

.margin-bottom-3 {
    margin-bottom: 60px;
}

.margin-bottom-4 {
    margin-bottom: 80px;
}

.margin-left-0 {
    margin-left: 0;
}

.margin-left-1 {
    margin-left: 20px;
}

.margin-left-2 {
    margin-left: 40px;
}

.margin-right-0 {
    margin-right: 0;
}

.margin-left-1 {
    margin-right: 20px;
}

.margin-left-2 {
    margin-right: 40px;
}

.auto-updatable button.postfix {
    min-width: 80px;
}

.auto-updatable.autoupdate-enabled button.postfix {
    background-color: #13659C;
    color: #fff;
}

hr.separator {
    border: 0 none;
    border-bottom:1px solid #dfdfdf;
    margin: 1em 0;
    padding: 0;
}

hr.separator.dotted {
    border-bottom:1px dotted #dfdfdf;
}

.text-security-disc {
    font-family: dotsfont !important;
    font-size: 10px;
}

.text-security-disc::-webkit-input-placeholder {
    font-family: Verdana, Arial, sans-serif !important;
    font-size: 13px;
}

.text-security-disc::-ms-input-placeholder {
    font-family: Verdana, Arial, sans-serif !important;
    font-size: 13px;
}

.text-security-disc::-moz-placeholder {
    font-family: Verdana, Arial, sans-serif !important;
    font-size: 13px;
}

.text-security-disc::placeholder {
    font-family: Verdana, Arial, sans-serif !important;
    font-size: 13px;
}

body {
    background-color:transparent;
    color: #000000;
    font-family:Verdana,Arial,sans-serif; 
    font-size:13px
}
fieldset {border:1px solid silver; border-radius:3px; padding:10px}
h3 {
    margin:1px; 
    padding:1px; 
    font-size:13px;
}

.generic-box .box-title,
.hdr-sub1 {
    font-size: 18px;
    font-weight: bold;
}

.sub-title {
    font-size:14px;
    margin-bottom: 5px;
}

.link-style,
a {
    text-decoration: underline;
    color: #222;
    transition: all 0.3s;
    cursor: pointer;
}
.link-style:hover,
a:hover{
    color: #13659C;
}

.margin-top {
    margin-top: 20px;
}

*:focus {
    outline: none !important;
}

input:not([type=checkbox]):not([type=radio]):not([type=button]):not(.select2-search__field) , select {
    min-width: 0;
    width: 100%;
    border-radius: 2px;
    border: 1px solid silver;
    padding: 4px;
    padding-left: 4px;
    font-family: Verdana,Arial,sans-serif;
    line-height: 20px;
    height: 30px;
    box-sizing: border-box;
    background-color: white;
    color: black;
    border-radius: 4px;
}

input:not([type=checkbox]):not([type=radio]):not([type=button]).w30 , select.w30 {
    width: 30%;
}

input:not([type=checkbox]):not([type=radio]):not([type=button]).w50 , select.w50 {
    width: 50%;
}

input:not([type=checkbox]):not([type=radio]):not([type=button]).w95 , select.w95 {
    width: 95%;
}

input[readonly]:not([type=checkbox]):not([type=radio]):not([type=button]) {
    background-color: #efefef;
    color: #999999;
    cursor: not-allowed;
}

textarea[readonly] {
    background-color: #efefef;
}

/*input.select2-search__field {
    height: auto;
    width: auto;
    border: 0 none;
    padding: 0;
}*/

.copy-to-clipboard-block textarea {
    width: 100%;
    height: 100px;
}

.copy-to-clipboard-block button {
    font-size: 14px;
    padding: 5px 8px;
    margin-bottom: 15px;
}

select[size]:not([size="1"]) {
    height: auto;
    line-height: 25px;
}

select , option {
    color: black;
}

select option {
    padding: 5px;
}

input:not([type=checkbox]):not([type=radio]):not([type=button]):disabled,
select:disabled,
select option:disabled,
select:disabled option, 
select:disabled option:focus,
select:disabled option:active,
select:disabled option:checked {
    background: #EBEBE4;
    color: #ccc;
    cursor: not-allowed;
}

select:disabled,
select option:disabled,
select:disabled option, 
select:disabled option:focus,
select:disabled option:active,
select:disabled option:checked  {
    text-decoration: line-through;
}

.option-group.option-disabled {
    color: #ccc;
    cursor: not-allowed;
}

button.no-layout {
    background: none;
    border: none;
}

.input-postfix-btn-group {
    display: flex;
    border: 1px solid darkgray;
    border-radius: 4px;
    overflow: hidden;
}

.input-postfix-btn-group input:not([type=checkbox]):not([type=radio]):not([type=button]) {
    flex: 1 1 0;
    border-radius: 0;
    border: 0 none;
    border-right: 1px solid darkgray;
    height: 28px;
}

.input-postfix-btn-group .prefix,
.input-postfix-btn-group .postfix {
    flex: none;
    min-width: 60px;
    box-sizing: border-box;
    padding: 0 10px;
    margin: 0;
    border: 0 none;
    background-color:#CDCDCD;
    line-height: 28px;
}

.param-wrapper-disabled .input-postfix-btn-group .prefix,
.param-wrapper-disabled .input-postfix-btn-group .postfix {
    color: #999999;
    pointer-events: none;
    cursor: not-allowed;
}

.param-wrapper.small .input-postfix-btn-group .prefix,
.param-wrapper.small .input-postfix-btn-group .postfix {
    min-width: 0;
}

.input-postfix-btn-group button {
    cursor: pointer;
}

.input-postfix-btn-group button:hover {
    border: 0 none;
    background-color: #13659C;
    color: white;
}


.param-wrapper span .checkbox-switch {
    top: 2px;
}

.param-wrapper.align-right {
    float: right;
}

.param-wrapper.align-right > .container > .main-label {
    width: auto;
}

.wpinconf-check-wrapper {
    flex: none;
    width: 100px;
}

#wrapper_item_subsite_id.param-wrapper-disabled,
#wrapper_item_subsite_owr_id.param-wrapper-disabled,
#wrapper_item_subsit_owr_slug.param-wrapper-disabled,
#wrapper_item_users_mode.param-wrapper-disabled {
    display: none;
}

.btn-group {
    display: inline-flex;
    border: 1px solid silver;
    border-radius: 5px;
    overflow: hidden;
}

.btn-group button {
    flex: 1 1 0;
    background-color: #E4E4E4; 
    border: 0 none !important;
    border-right: 1px solid silver !important;
    padding: 6px; 
    cursor: pointer; 
    float: left;
    font-size: 14px;
}

.overwrite_sites_list {
    display: flex;
    flex-direction: column;
    row-gap: 20px;
}

.param-form-type-sitesowrmap .overwrite_site_item {
    display: flex;
    flex-wrap: wrap;
    gap: 5px 20px;
}

.param-form-type-sitesowrmap .overwrite_site_item .del_item {
    float: right;
    font-size: 25px;
    line-height: 1;
}

.param-form-type-sitesowrmap .overwrite_site_item .del_item.disabled {
    color: silver;
}

.param-form-type-sitesowrmap .overwrite_site_item > .col {
    flex: 1 1 0;
}
.param-form-type-sitesowrmap .overwrite_site_item.title > .col {
    border-bottom: 1px solid #D3D3D3;
    padding-bottom: 5px;
    font-weight: bold;
}

.param-form-type-sitesowrmap .overwrite_site_item > .col.del {
    flex-grow: 0;
    font-size: 18px;
    border:none;
}

.param-form-type-sitesowrmap .overwrite_sites_list.no-multiple .overwrite_site_item > .col.del,
.param-form-type-sitesowrmap .overwrite_sites_list.no-multiple .overwrite_site_item.add_item {
    display: none;
}

.param-form-type-sitesowrmap .overwrite_site_item > .full {
    flex: 0 0 100%;
}

.param-form-type-sitesowrmap .target_select_wrapper {
    position: relative;
}

.param-form-type-sitesowrmap .target_select_wrapper .new-slug-wrapper {
    position: absolute;
    top: 0;
    right: 22px;
    width: 280px;
}

.param-form-type-sitesowrmap .target_select_wrapper .new-slug-wrapper  input {
    background: #EFEFEF;
    border-radius: 0;
}

.param-form-type-sitesowrmap .sub-note {
    word-wrap: anywhere;
}

.param-form-type-sitesowrmap .sub-note .site-slug {
    font-weight: bold;
    display: inline-block;
    padding: 2px;
    background: #EFEFEF;
    border-radius: 2px;
}

.btn-group.small button {
    padding: 3px 7px 3px 7px;
    font-size: 11px;
}

.btn-group button:last-child {
    border-right: none !important; 
}

.btn-group:after {
    content: "";
    clear: both;
    display: table;
}

.btn-group button:hover,
.btn-group button.active {
    background-color: #13659C;
    color: #FFF;
}

.box {
    border: 1px solid silver;
    padding: 10px;
    background: #f9f9f9;
    border-radius:2px;
}

.box *:first-child {
    margin-top: 0;
}

.box *:last-child {
    margin-bottom: 0;
}

.box.warning,
.box.warning-easy {
    color: maroon;
}

.box.warning {
    border-color: maroon;
}

/* ============================
COMMON VIEWS
 ============================ */
body,
div#content,
form.content-form {
    line-height: 1.5;
}

/*Lets revisit this later.  Right now anything over 900px gives the overall feel of an elongated flow and the
inputs look too spread out. If we can iron out some of those issues with multi-columns and the notices view better
then we can try and work more towards a full fluid layout*/
#content {
    border:1px solid #CDCDCD; 
    margin: 20px auto; 
    border-radius:2px;
    box-shadow:0 8px 6px -6px #999;
    font-size:13px;
    width: calc(900px + 42px);
    max-width: calc(100vw - 40px);
    box-sizing: border-box;
}

.debug-params div#content {
    margin: 20px; 
}

#content-inner {
    margin: 20px;
    position: relative;
}

#content-loader-wait {        
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
}

#body-step4 #content-inner {
    padding-bottom: 0;
}

div.logfile-link {float:right; font-weight:normal; font-size:11px; font-style:italic}

/* Header */
table.header-wizard {width:100%; box-shadow:0 5px 3px -3px #999; background-color:#E0E0E0; font-weight:bold}
.wiz-dupx-version {
    white-space:nowrap; 
    color:#777; 
    font-size:11px; 
    font-style:italic; 
    text-align:right;  
    padding:5px 15px 5px 0; 
    line-height:14px; 
    font-weight:normal
}

.wiz-dupx-version a,
.wiz-dupx-version .link-style  { 
    color:#999; 
}

.wiz-dupx-version a:hover,
.wiz-dupx-version .link-style:hover  { 
    color: #333; 
}

div.dupx-debug-hdr {padding:5px 0 5px 0; font-size:16px; font-weight:bold}
div.dupx-branding-header {font-size:26px; padding: 10px 0 7px 15px;}
i.main-help-icon {
    color: #13659C !important;
    display:inline-block;
    padding-left:4px;
}


div.dupx-modes span.mode_standard {color:black}
div.dupx-modes span.mode_overwrite {color:maroon}
div.dupx-modes span.mode_restore_bk {color:maroon}

.dupx-pass {display:inline-block; color:green;}
.dupx-fail {display:inline-block; color:#AF0000;}
.dupx-warn {display:inline-block; color:#555;}
.dupx-notice {display:inline-block; color:#000;}
i[data-tooltip].fa-question-circle {cursor: pointer; color:#888888}


.pro-flag {
    color:#333;
    font-size:14px;
    font-weight:bold;
    cursor:pointer;
    vertical-align:sub;
    padding: 4px 5px;
}

.pro-flag:hover {
    color:maroon;
}

.pro-flag-close {
    padding:0 !important;
}

ul.pro-tip-flag {
    margin:10px 0 10px  -15px !important;
}

ul.pro-tip-flag li {
    margin:3px 0 3px 0;
}

.pro-tip-link {
    margin-top:20px;
    background:#dfdfdf;
    padding:5px;
    text-align:center;
    font-style:italic;
}

sup.hlp-pro-lbl,
sup.small-pro-lbl,
sup.hlp-new-lbl
{
    color:#fff !important;
    background:#d59999;
    border-radius:2px;
    padding:1px 3px 1px 3px;
    font-size:9px !important;
    cursor: pointer;
}

sup.small-pro-lbl {
    font-size:8px !important;
}

sup.hlp-new-lbl {
   font-size:7.75px !important;
   padding:0 3px 1px 2px;
}

.status-badge {
    border-radius:4px; 
    color:#fff; 
    padding:0 3px 0 3px;  
    font-size:11px; 
    min-width:30px; 
    text-align:center; 
    font-weight:normal;
}
.status-badge.right {
    float: right; 
}
.status-badge.pass,
.status-badge.good,
.status-badge.success {
    background-color:#418446
}
.status-badge.pass::after {
    content: "Pass"
}
.status-badge.good::after {
    content: "Pass"
}
.status-badge.success::after {
    content: "Success"
}
.status-badge.fail {
    background-color:maroon;
}
.status-badge.fail::after {
    content: "Fail"
}
.status-badge.hwarn {
    background-color: #a15e19;
}
.status-badge.hwarn::after {
    content: "Warn"
}
.status-badge.warn {
    background-color: #555555;
}
.status-badge.warn::after {
    content: "Notice"
}

.default-btn,
.secondary-btn {
    transition: all 0.2s ease-out;
    color: #FEFEFE;
    font-size: 16px;
    border-radius: 5px;
    padding: 7px 15px;
    background-color: #13659C;
    border: 1px solid gray;
    line-height: 18px;
    text-decoration: none;
    display: inline-block;
    white-space: nowrap;
    min-width: 100px;
    text-align: center;
}

.default-btn.small,
.secondary-btn.small {
    font-size: 13px;
    padding: 3px 10px;
    min-width: 80px;
}

.default-btn:hover {
    color: #13659C;
    border-color: #13659C;
    background-color: #FEFEFE;
}

.default-btn.disabled,
.default-btn:disabled {
    color:silver;         
    background-color: #EDEDED;
    border: 1px solid silver;
}

.secondary-btn {
    color: #333333;         
    background-color: #EDEDED;
    border: 1px solid #333333;
}

.secondary-btn:hover {
    color: #FEFEFE;         
    background-color: #999999;
}

.log-ui-error {padding-top:2px; font-size:13px}
#progress-area {
    padding:5px; 
    margin:150px 0; 
    text-align:center;
}
.progress-text {font-size:1.7em; margin-bottom:20px}
#secondary-progress-text { font-size:.85em; margin-bottom:20px }
#progress-notice:not(:empty) { color:maroon; font-size:.85em; margin-bottom:20px; }

#ajaxerr-data {
    min-height: 300px;
}

#ajaxerr-data .pre-content,
#ajaxerr-data .html-content {
    padding:6px; 
    box-sizing: border-box;
    width:100%; 
    border:1px solid silver; 
    border-radius:3px;
    background-color:#F1F1F1; 
    font-size:11px; 
    overflow-y:scroll; 
    line-height:20px
}

#ajaxerr-data .pre-content {
    height:300px;
}

#header-main-wrapper {
    position: relative;
    padding:0 0 5px 0; 
    border-bottom:1px solid #D3D3D3; 
    margin: 0 0 20px 0;
    display: flex;
}

#header-main-wrapper .dupx-logfile-link {
    font-weight:normal; 
    font-style:italic; 
    font-size:11px;
    position: absolute;
    bottom: 2px;
    right: 0;
}


#header-main-wrapper .hdr-main {
    font-size:22px; 
    font-weight:bold; 
    flex: 1 1 auto;
}

#header-main-wrapper .hdr-secodary {
    flex: 0 1 auto;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-width: 150px;
}

.hdr-secodary .installer-log {
    font-size: 12px;
    font-style: italic;
    text-align: right;
}

.hdr-secodary .dupx-modes {
    text-align: right;
}

#installer-switch-wrapper  {
    text-align:right
}

#installer-switch-wrapper .btn-group {
    width: 210px;
    margin-top:7px;
}

.s1-switch-template-btn {
    font-size:13px !important
}

.generic-box { 
    border: 1px solid #DEDEDE;
    border-radius: 2px;
    margin-bottom: 20px;
}

.generic-box .box-title { 
    padding: 4px 7px;
    border-bottom: 1px solid #DEDEDE;
    background-color:#f9f9f9; 
    border-radius:2px 2px 0 0;
}

.generic-box .box-content { 
    padding: 20px;
}

.generic-box .box-content *:first-child {
    margin-top: 0;
}

.generic-box .box-content *:last-child {
    margin-bottom: 0;
}

div.sub-header {
    font-size:11px; 
    font-style:italic; 
    font-weight:normal
}
.hdr-main .step { 
    color:#DB4B38  
}

.hdr-sub1 {
    border:1px solid #D3D3D3;
    padding: 4px 7px;
    background-color:#E0E0E0;
    border-radius:2px 2px 0 0;
    user-select:none;
}

.hdr-sub1.open {
    border-radius: 2px;
    margin-bottom: 20px;
}

.hdr-sub1 a {cursor:pointer; text-decoration: none !important}
.hdr-sub1 i.fa,
.hdr-sub1 i.fas,
.box-title i.fa,
.box-title i.fas {
    font-size:15px; 
    display:inline-block; 
    margin-right:5px; 
    position: relative;
    bottom: 1px;
}

.hdr-sub1 .status-badge {
    margin-top: 4px;
}

.hdr-sub1-area {
    border: 0 solid #D3D3D3;
    border-top: 0 none;
    border-radius: 0 0 2px 2px;
    padding: 20px;
    margin-bottom: 20px;
    position: relative;
    background-color:#fff;
}

.hdr-sub1-area.tabs-area {
    padding: 5px 5px 0 5px;
}

.hdr-sub1-area.tabs-area .ui-tabs-nav {
    border-radius: 0;
    border: 0 none;
}

.hdr-sub1-area.tabs-area .ui-tabs {
    margin: 0;
    padding: 0;
    border: 0 none;
}

.hdr-sub1-area.tabs-area .ui-tabs-tab {
    margin: 3px 5px 0 0;
}

.hdr-sub1-area.tabs-area .ui-tabs-panel {
    position: relative;
    padding:15px;
}

.hdr-sub2 {font-size:15px; padding:2px 2px 2px 0; font-weight:bold; margin-bottom:5px; border:none}
.hdr-sub3 {font-size:15px; padding:2px 2px 2px 0; border-bottom:1px solid #e2e2e2; font-weight:bold; margin-bottom:10px;}
.hdr-sub3.warning::before {
    content: "\f071";
    font-family: "Font Awesome 5 Free";
    font-weight: 900;
    font-style: normal;
    font-variant: normal;
    text-rendering: auto;
    line-height: 1;
    font-size: 14px;
    color: #AF0000;
}
.hdr-sub4 {font-size:15px; padding:7px; border:1px solid #D3D3D3;; font-weight:bold; background-color:#e9e9e9;}
.hdr-sub4:hover  {background-color:#dfdfdf; cursor:pointer}
.toggle-hdr:hover {cursor:pointer; background-color:#f1f1f1; border:1px solid #dcdcdc; }
.toggle-hdr:hover a{color:#000}
.ui-widget-header {border: none; border-bottom: 1px solid #D3D3D3 !important; background:#fff}


[data-type="toggle"] > i.fa,
i.fa.fa-toggle-empty { min-width: 8px; }

/* ============================
NOTICES
============================ */
/* step messages */
#page-top-messages { 
    padding: 0 20px; 
}

.notice {
    background: #fff;
    border:1px solid #dfdfdf;
    border-left: 4px solid #fff;
    margin: 5px 0;
    padding:0;
    border-radius: 2px;
    font-size: 12px;
}

.section .notice:first-child {
    margin-top: 0;
}

.section .notice:last-child {
    margin-bottom: 0;
}

.notice.next-step {
    margin: 20px 0;
    padding: 10px;
}

.notice-report {
    border-left: 4px solid #fff;
    padding-left: 0;
    padding-right: 0;
    margin-bottom: 4px;
}

.notice-report .title:hover {
    cursor:pointer;
    background-color:#efefef;
}

.next-step .title-separator {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid lightgray;
}

.notice .info pre {
    margin: 0;
    padding: 0 0 10px 0;
    overflow: auto;
}

.notice-report .title {
    padding:7px;
    background-color:#f4f4f4;
}

.notice-report .title.close {
    padding-bottom: 5px;
}

.notice-report .info {
    border-top: 1px solid #dedede;
    padding: 10px;
    background: #FFFFF3;
}

.notice-report .info *:first-child {
    margin-top: 0;
}

.notice-report .info *:last-child{
    margin-bottom: 0;
}

.notice-report .info pre {
    font-size: 11px;
}

.notice.l-info,
.notice.l-notice {
    border-left-color: #197b19;
}
.notice.l-swarning {
    border-left-color: #636363;
}
.notice.l-hwarning {
    border-left-color: #636363;
}
.notice.l-critical {
    border-left-color: maroon;
}
.notice.l-fatal {
    border-left-color: #000000;
}

.notice.next-step {
    position: relative;
}

.notice.next-step.l-info,
.notice.next-step.l-notice {
    border-color: #197b19;
}
.notice.next-step.l-swarning {
    border-color: #636363;
}
.notice.next-step.l-hwarning {
    border-color: #636363;
}
.notice.next-step.l-critical {
    border-color: maroon;
}
.notice.next-step.l-fatal {
    border-color: #000000;
}

.notice.next-step > .title {
    padding-left: 30px;
}

.notice.next-step > .fas {
    display: block;
    position: absolute;
    height: 20px;
    width: 20px;
    line-height: 20px;
    text-align: center;
    color: white;
    border-radius:2px;
}

.notice.next-step.l-info > .fas,
.notice.next-step.l-notice > .fas {
    background-color: #197b19;
}
.notice.next-step.l-swarning > .fas {
    background-color: #636363;
}
.notice.next-step.l-hwarning > .fas {
    background-color: #636363;
}
.notice.next-step.l-critical > .fas {
    background-color: maroon;
}
.notice.next-step.l-fatal > .fas{
    background-color: #000000;
}

.report-sections-list .section {
    border: 1px solid #DFDFDF;
    margin-bottom: 25px;
    box-shadow: 4px 8px 11px -8px rgba(0,0,0,0.41);
}

.report-sections-list .section > .section-title {
    background-color: #efefef;
    padding: 3px;
    font-weight: bold;
    text-align: center;
    font-size: 14px;
}

.report-sections-list .section > .section-content {
    padding: 5px;
}

.notice-level-status {
    border-radius:2px;
    padding: 2px;
    margin: 1px;
    font-size: 10px;
    display: inline-block;
    color: #FFF;
    font-weight: bold;
    min-width:55px;
}

.notice-level-status.l-info,
.notice-level-status.l-notice {background: #197b19;}
.notice-level-status.l-swarning {background: #636363;}
.notice-level-status.l-hwarning {background: #636363;}
.notice-level-status.l-critical {background: maroon;}
.notice-level-status.l-fatal {background: #000000;}

/*Adv Opts */
.dupx-opts .param-wrapper {
    padding: 5px 0;
}
.dupx-opts .param-wrapper .param-wrapper {
    padding: 0;
}

.dupx-opts .param-wrapper.param-form-type-hidden{
    margin: 0;
    padding: 0;
    display: none;
}

.param-wrapper-disabled {
    color: #999;
}

.param-wrapper > .container {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: center;
    min-height: 30px;
}

.param-wrapper > .container > .main-label {
    flex: none;
    width: 200px;
    font-weight: bold;
    line-height: 1.5;
    box-sizing: border-box;
    padding-right: 5px;
}

.param-wrapper.has-main-label > .sub-note {
    margin-left: 200px;
}

#tabs-wp-config-file .param-wrapper > .container > .main-label {
    width: 310px;
}

#tabs-wp-config-file .param-wrapper.has-main-label > .sub-note {
    margin-left: 310px;
}

#tabs-wp-config-file div.help-target {
    padding-top:10px;
}

.param-wrapper > .container .input-container {
    flex: 1 1 auto;
}

.param-wrapper.small > .container .input-container {
    max-width: 100px;
}

/*.param-wrapper.medium > .container .input-container {
    max-width: 300px;
}*/

.param-wrapper.large > .container .input-container {
    max-width: 500px;
}

.param-wrapper.full > .container .input-container {
    max-width: none;
}

/*
.dupx-opts > .param-wrapper:nth-child(2n+1) {
    background-color: #EAEAEA;
}

.dupx-opts > .param-wrapper:nth-child(2n) {
    background-color: #F6F6F6;
}*/

.param-form-type-radio .option-group {
    display: inline-block;
    min-width: 140px;
}

.param-form-type-radio.group-block .option-group {
    display: block;
    line-height: 30px;
}

.param-wrapper .sub-note {
    display: block;
    font-size: 11px;
    margin-top:6px;
}

.param-wrapper .option-group .sub-note {
    line-height: 1.1;
    margin-top: 0;
    margin-bottom: 8px;
    color: #000000;
}

table.dupx-opts {width:100%; border:0px;}
table.dupx-opts td{padding:3px;}
table.dupx-opts td:first-child{width:125px; font-weight: bold}
table.dupx-advopts td:first-child{width:125px;}
table.dupx-advopts label.radio {width:50px; display:inline-block}
table.dupx-advopts label {white-space:nowrap; cursor:pointer}
table.dupx-advopts-space {line-height:24px}
table.dupx-advopts tr {vertical-align:top}

div.error-pane {border:1px solid #efefef; border-left:4px solid maroon; padding:0 0 0 10px; margin:2px 0 10px 0}
div.dupx-ui-error {padding-top:2px; font-size:13px; line-height: 20px}

.main-form-content {
    min-height: 300px;
}

.footer-buttons {
    display: flex;
    width: 100%;
}

.footer-buttons .content-left,
.footer-buttons .content-center {
    flex: 1;
}
.footer-buttons .content-center {
    text-align: center;
}

form#form-debug {display:block; margin:10px auto; width:750px;}
form#form-debug a {display:inline-block;}
form#form-debug pre {margin-top:-2px; display:none}
small.info {font-style:italic}

/*Dialog Info */
div.dlg-serv-info {line-height:22px; font-size:12px}
div.dlg-serv-info label {display:inline-block; width:200px; font-weight: bold}
div.dlg-serv-info div.hdr {font-weight: bold; margin-top:5px; padding:2px 5px 2px 0; border-bottom: 1px solid #777; font-size:14px}

/* ============================
UI TABS OVERWRITE
 ============================ */

.ui-tabs .ui-tabs-nav .ui-tabs-anchor {
    display: inline-block;
    width: 100%;
    box-sizing: border-box;
    text-align: center;
}

.ui-tabs .ui-tabs-nav li {
    min-width: 150px;
}

/* ============================
INIT 1:SECURE PASSWORD
============================ */
#pass-quick-help-info {
    font-size:13px;
    line-height:22px;
}

#pass-quick-help-info ul {
    margin:5px;
}

#pass-quick-help-info li {
    padding:2px;
    list-style-type:circle;
}

.pass-quick-help-note {
    text-align:center;
    font-size:11px;
    font-style:italic;
}

#wrapper_item_secure-pass .sub-note,
#wrapper_item_secure-archive .sub-note {
    text-align: right;
}

/* ============================
STEP 1 VIEW
 ============================ */

div.overview-description {padding:0 !important}
div.overview-description .details {margin:0; padding:0}
div.overview-description .details table td {padding:4px}
div.overview-description .details table td:first-child {display:inline-block; width:75px; font-weight:bold}
div.overwrite {font-weight:italic; font-size:11px; margin-top:3px;}
div.overview-description .details .help-icon {float:right; margin-top:-7px}

#s1-area-setup-type label {
    cursor:pointer;
}
.s1-setup-type-sub {padding:5px 0 0 25px; display:none}
#s1-area-archive-file .ui-widget.ui-widget-content {border: 0px solid #d3d3d3}
#s1-area-setup-tabs  .ui-widget.ui-widget-content {border: 0px solid #d3d3d3}
table.s1-archive-local {width:100%}
table.s1-archive-local td {padding:4px 4px 4px 4px}
table.s1-archive-local td:first-child {font-weight:bold; width:55px}
div.s1-err-msg i {color:maroon}
#base-setup-area {
    padding:5px 0 0 0;
}



div#s1-multisite p.note {font-size:10px; font-style:italic; text-align:center; color:#777; margin:30px 0 0 0}

div#validate-area-header { background-color:#E0E0E0}
div#validate-area {border:2px dashed silver; border-top:none}
#validate-area .info,
#validate-area #hard_warning_action {
    font-size: 12px;
    text-align: center;
    margin: 0;
    font-style: italic;
}

div#validate-no-result {
    padding:30px 0 40px 0;
    font-weight:bold;
}

#validate-area .info {line-height:20px}
div.s1-validate-flagged-tbl-list {max-height:500px; border:1px solid #d3d3d3; overflow-y:scroll; background:#efefef;}
table.s1-validate-sub-status td:first-child {font-weight:bold}
table.s1-validate-sub-status td {padding:1px 5px 1px 5px}
table.s1-validate-sub-status td small {font-weight:normal}

table.s1-checks-area {width:100%; margin:0; padding:0}
table.s1-checks-area td.title {font-size:16px; width:100%}
table.s1-checks-area td.title small {font-size:11px; font-weight:normal}
table.s1-checks-area td.toggle {font-size:11px; margin-right:7px; font-weight:normal}


div.s1-reqs {background-color:#efefef; border:1px solid silver; border-radius:2px; padding-bottom:4px}
div.s1-reqs div.header {background-color:#E0E0E0; color:#000;  border-bottom: 1px solid silver; padding:2px; font-weight:bold }
div.s1-reqs div.status {
    float:right; 
    border-radius:2px; 
    color:#fff; 
    padding:0 3px 0 3px; 
    margin:4px 5px 0 0; 
    font-size:11px; 
    min-width:30px; 
    text-align:center;
}
div.s1-reqs div.pass {background-color:green;}
div.s1-reqs div.fail {background-color:#636363;}
div.s1-reqs div.title {
    padding:3px 3px 3px 5px; 
    font-size:13px;
    line-height: 20px;
}
div.s1-reqs div.title:hover {background-color:#dfdfdf; cursor:pointer}
div.s1-reqs div.info {padding:8px 8px 20px 8px; background-color:#fff; display:none; line-height:18px; font-size: 12px}
div.s1-reqs div.info a {color:#485AA3;}
select#archive_engine {width:90%; cursor:pointer}


#wrapper_item_accept-warnings,
#wrapper_item_accept-hwarn-valid
{
    margin-left:30px;
    font-size: 15px;
}

#wrapper_item_accept-warnings span.label-checkbox {
    cursor:pointer !important;
}

#advanced-toggle-info {
    color: #13659C;
    font-size: 15px;
}

#advanced-mode-info {
    background-color: #f1f1f1;
    border: 1px solid #dcdcdc;
    border-radius:2px;
    padding: 10px;
    margin: 20px 0;
}
div.test-wrapper {
    cursor:pointer;
}

/*Terms and Notices*/
div.s1-accept-check label{cursor:pointer;}
div#s1-warning-msg {
    padding:5px;
    font-size:12px; 
    color:#333; 
    line-height:14px;
    font-style:italic; 
    overflow-y:scroll; 
    height:460px; 
    border:1px solid #dfdfdf; 
    background:#fff; 
    border-radius:2px
}
div.s1-accept-check {padding:3px; font-size:14px; font-weight:normal;}
input#accept-warnings, div.s1-accept-check input[type=checkbox] {height: 17px; width:17px}
div#wrapper_item_accept-warnings {margin-left:30px}

#tabs-other .param-wrapper .sub-note {
    margin-bottom: 10px;
}

#wrapper_item_view_mode {
    margin-top:-3px;
}

#wrapper_item_view_mode .container {
    min-height: 0;
}

#wrapper_item_view_mode .btn-group {
    width: 120px;
}

#wrapper_item_subsit_owr_slug .input-postfix-btn-group .prefix,
#wrapper_item_subsit_owr_slug .input-postfix-btn-group .postfix {
    box-sizing: border-box;
    min-width: 0;
    max-width: 300px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
button#s1-deploy-btn {margin-top:60px}
div.required-txt {margin:-10px 0 0 15px}
input.pwd-simulation {height:29px !important; padding-top:6px !important}

#db-install-dialog-confirm .copy-link {
    font-size: 13px;
    padding: 4px;
    line-height: 1;
    height: auto;
    min-height: 0;
    min-width: 90px;
}

div#tabs-database {
    min-height:750px;
}

table.dup-s1-confirm-dlg td:first-child {
    font-weight:bold;
    width:125px;
}
table.dup-s1-confirm-dlg td:last-child {
    font-style:italic;
}

div#wrapper_item_remove-users-without-permissions {
    margin-top:-15px;
}

/*cPanel DB */
.s1-cpanel-off,
.s1-cpanel-login {
    padding: 10px;
    color: #000;
    text-align: center;
    margin:0 0 15px 0;
    border: 1px solid silver;
    border-radius: 2px;
    background-color: #ebadad;
    font-size: 14px;
    line-height: 22px;
    font-weight:bold;
}

.s1-cpanel-off small,
.s1-cpanel-login small {
    font-weight:normal;
}


.s1-cpanel-login {
    background-color: #8ab98a !important;
}
.s1-cpanel-whatis {
    width:100%;
    text-align:center;
    font-size:12px;
    font-style:italic;
    margin:-15px 0 10px 0;
}

.s1-cpanel-whatis a{
    color:#888 !important;
}


/* ============================
STEP 2 VIEW
============================ */

/*Toggle Buttons */
div.s2-btngrp {text-align:center; margin:0 auto 10px auto}
div.s2-btngrp input[type=button] {font-size:14px; padding:6px; width:120px; border:1px solid silver;  cursor:pointer}
div.s2-btngrp input[type=button]:first-child {border-radius:5px 0 0 5px; margin-right:-2px}
div.s2-btngrp input[type=button]:last-child {border-radius:0 5px 5px 0; margin-left:-4px}
div.s2-btngrp input[type=button].active {background-color:#13659C; color:#fff;}
div.s2-btngrp input[type=button].in-active {background-color:#E4E4E4; }
div.s2-btngrp input[type=button]:hover {border:1px solid #999}

/*Basic DB */
select#dbname-select {width:100%; border-radius:2px; height:20px; font-size:12px; border:1px solid silver;}
div#s2-dbrefresh-basic {float:right; font-size:12px; display:none;  font-weight:bold; margin:5px 5px 1px 0}
div#s2-dbrefresh-cpnl {float:right; font-size:12px; display:none; font-weight:bold; margin:5px 5px 1px 0}
div#s2-db-basic-overwrite {border: 1px solid silver; margin:0 0 20px 0; padding:10px; background:#f9f9f9; border-radius:2px}
div#s2-db-basic-overwrite div.warn-text {font-size:12px; padding:5px 0 5px 0; color:maroon}
div#s2-db-basic-overwrite div.btn-area {text-align: right; margin:5px 0}
input.overwrite-btn {
    cursor:pointer; color:#fff; font-size:13px; border-radius:5px;  padding:5px 20px 4px 20px;
    background-color:#989898; border:1px solid #777;
}

div.s2-gopro {color: black; margin-top:10px; padding:0 20px 10px 20px; border: 1px solid silver; background-color:#F6F6F6; border-radius:2px}
div.s2-gopro h2 {text-align: center; margin:10px}
div.s2-gopro small {font-style: italic}
.s2-cpnl-panel-no-support {text-align:center; font-size:18px; font-weight:bold; line-height:30px; margin-top:40px}
td#cpnl-prefix-dbname {width:10px}
td#cpnl-prefix-dbuser {width:10px; white-space:normal}
div#s2-cpnl-area div#cpnl-host-warn {white-space:normal; font-size:11px; display:none; font-style: italic}
a#s2-cpnl-status-msg {font-size:11px}
span#s2-cpnl-status-icon {display:none}
div#s2-cpnl-connect {margin:auto; text-align:center; margin:10px 0 0 0}
div#s2-cpnl-status-details {
    border: 1px solid #AF0000;
    border-radius:2px;
    background-color: #f9f9f9;
    padding: 20px;
    margin-top: 20px;
}
div#cpnl-dbname-prefix {display:none; float:left; margin-top:3px;}
span#s2-cpnl-db-opts-lbl {font-size:11px; font-weight:normal; font-style:italic}
div#s2-cpnl-dbname-area2 table {border-collapse: collapse; width: 100%}
div#s2-cpnl-dbname-area2 table td {padding:0 !important; margin:0; border:0}
div#s2-cpnl-dbname-area2 table td:first-child {vertical-align:bottom;}
div#s2-cpnl-dbname-area2 table td:nth-child(2) {width:100%; padding-right:0 !important}
div#s2-cpnl-dbuser-area2 table {border-collapse: collapse; width: 100%}
div#s2-cpnl-dbuser-area2 table td {padding:0 !important; margin:0; border:0}
div#s2-cpnl-dbuser-area2 table td:first-child {vertical-align:bottom;}
div#s2-cpnl-dbuser-area2 table td:nth-child(2) {width:100%; padding-right:0 !important}

/*DATABASE CHECKS */
.s2-dbtest-area {
    min-height:110px
}
.s2-dbtest-area input[type=button] {font-size:11px; height:20px; border:1px solid gray; border-radius:3px; cursor:pointer}
.s2-dbtest-area small.db-check {color:#000; text-align:center; padding:3px; font-size:11px; font-weight:normal }
.s2-dbtest-area div.message {
    padding:10px 10px 10px 10px; 
    margin:5px auto 5px auto; 
    text-align:center; 
    font-style:italic; 
    font-size:15px; 
    line-height:22px; 
    width:100%;
}
.s2-dbtest-area div.sub-message {padding:5px; text-align:center; font-style:italic; color:maroon}
.s2-dbtest-area div.error-msg {color:maroon}
.s2-dbtest-area div.success-msg {color:green}
.s2-dbtest-area pre {font-family:Verdana,Arial,sans-serif; font-size:13px; margin:0; white-space:normal;}

div.s2-reqs-hdr {border-radius:4px 4px 0 0; border-bottom:none}
div.s2-notices-hdr {border-radius:0; border-bottom:1px solid #D3D3D3; }
div#s2-reqs-all {display:none}
div#s2-notices-all {display:none}

div.s2-reqs {background-color:#efefef; border:1px solid #D3D3D3; border-top:none}
div.s2-reqs div.status {
    margin:4px 7px 0 0;
}
div.s2-reqs div.title {padding:3px 8px 3px 20px; font-size:13px; background-color:#f1f1f1; border-top: 1px solid #D3D3D3;}
div.s2-reqs div.title:hover {background-color:#dfdfdf; cursor:pointer}
div.s2-reqs div.info {padding:4px 12px 15px 12px;; background-color:#fff; display:none; line-height:18px; font-size: 12px}
div.s2-reqs div.info a {color:#485AA3;}
div.s2-reqs div.info ul {padding-left:25px}
div.s2-reqs div.info ul li {padding:2px}
div.s2-reqs div.info ul.vids {list-style-type: none;}
div.s2-reqs div.sub-title{border-bottom: 1px solid #d3d3d3; font-weight:bold; margin:7px 0 3px 0}

div.s2-reqs10 table {margin-top:5px;}
div.s2-reqs10 table td {padding:1px;}
div.s2-reqs10 table td:first-child {font-weight:bold; padding-right:10px}
div.s2-reqs40 div.db-list {height:70px; width:95%; overflow-y:scroll; padding:2px 5px 5px 5px; border:1px solid #d3d3d3;}
div.s2-reqs60 div.tbl-list {padding:2px 5px 5px 5px; border:0 }
div.s2-reqs60 div.tbl-list b {display:inline-block; width:55px; }

div.s2-notice20 table.collation-list table {padding:2px;}
div.s2-notice20 table.collation-list td:first-child {font-weight:bold; padding-right:5px }

/*Warning Area and Message */
.s2-warning-emptydb {color:maroon; margin:2px 0 0 0; font-size:11px; display: none; white-space:normal; width: 550px}
.s2-warning-manualdb {color:#1B67FF; margin:2px 0 0 0; font-size:11px; display:none; white-space:normal; width: 550px}
.s2-warning-renamedb {color:#1B67FF; margin:2px 0 0 0; font-size:11px; display:none; white-space:normal; width: 550px}
#s2-tryagain {padding-top:50px; text-align:center; width:100%; font-size:16px; color:#444; font-weight:bold;}
table tr.param_item_tables_item {font-size:12px; font-family: monospace}

/* ============================
STEP 3 VIEW
============================ */
table.s3-opts {width:100%; border:0;}
table.s3-opts i.fa{font-size:16px}
table.s3-opts td{white-space:nowrap; padding:3px;}
table.s3-opts td:first-child{width:90px; font-weight: bold}
div.s3-allnonelinks {font-size:11px; float:right;}
div.s3-manaual-msg {font-style: italic; margin:-2px 0 5px 0}
.s3-warn {color:maroon; padding:10px 0 5px 0}

#plugins-filters {
    list-style: none;
    margin:0;
    padding: 0;
    font-size: 13px;
    float: left;
    color: #666;
}

#plugins-filters li {
    display: inline-block;
    margin: 0;
    padding: 0;
    white-space: nowrap;
}

#plugins-filters li a {
    color: #0073aa;
    line-height: 2;
    padding: .2em;
    text-decoration: none;
    text-transform: capitalize;
}

#plugins-filters li a:hover {
    color: #00a0d2;
}

#plugins-filters li a .count {
    color: #555d66;
    font-weight: 400;
}

#plugins-filters li a.current {
    font-weight: 600;
    border: none;
    color: #000;
}

#plugins-filters li::after {
    content: '|';
}

#plugins-filters li:last-child::after {
    content: '';
}

#wrapper_item_tables {
    min-height: 400px;
    max-height: calc(100vh - 400px);
    overflow-y: auto;
    margin-top:10px;
}

.list_table_selector {
    width: 100%;
}

.list_table_selector th {
    background-color: #cecece;
    padding: 6px 10px 6px 10px;
}

.list_table_selector .table-item:nth-child(odd) {
    background-color: #fbfbfb;
}

.list_table_selector .table-item:nth-child(even) {
    background-color: #ececec;
}

.list_table_selector .table-item td:first-child {
    border-left: 4px solid transparent;
}

.list_table_selector td {
    padding: 10px 5px;
    line-height: 1.5em;
}

.list_table_selector .check_input {
    text-align: center;
}

.list_table_selector .check_input input {
    margin: 0;
}

.list_table_selector .info {
    text-align:left;
}

.list_table_selector .table-item.active td:first-child {
    border-left: 4px solid #00a0d2;
}

.list_table_selector .table-item.active:nth-child(odd) {
    background-color: #ebfaff;
}
.list_table_selector .table-item.active:nth-child(even) {
    background-color: #c5effc;
}

.list_table_selector .action {
    text-align: center;
    min-width: 70px;
}

.list_table_selector .orig_status {
    text-align: center;
}

.param-wrapper.param-form-type-tablessel {
    padding: 0;
}




.list_table_selector .table-name {
    max-width: 275px;
    display: inline-block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-family: monospace;
    font-size: 12px;
}

.list-import-upt-tables {
    border-collapse: collapse
}

.list-import-upt-tables thead {
    position: -webkit-sticky;
    position: sticky;
    top: 0;
    background: #FFFFFF;
    z-index: 1000;
}

.list-import-upt-tables tfoot {
    position: -webkit-sticky;
    position: sticky;
    bottom: 0;
    background: #FFFFFF;
    z-index: 1000;
}

.list-import-upt-tables .name,
.list-import-upt-tables .info {
    width: 40%;
}

.list-import-upt-tables .info.toggle-all {
    text-align: right;
    font-weight: bold;
}

.list-import-upt-tables td {
    padding: 6px 0;
}

.list-import-upt-tables .action {
    width: 10%
}


.list-import-upt-tables .spacer {
    width: 15px;
    padding: 0
}

button#select-all-plugins {padding:1px; cursor:pointer}
button#unselect-all-plugins {padding:1px; cursor:pointer}
a.plugin-link {
    display: inline-block;
    max-width: 440px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space:nowrap;
    vertical-align:middle;
}


/* ============================
STEP 4 VIEW
============================ */

#page-step4 #content-inner {
    font-size: 12px;
}

.flex-final-button-wrapper {
    display: flex;
    margin-bottom: 20px;
    font-size: 13px;
}

.flex-final-button-wrapper .button-wrapper {
    padding-top: 3px;
}

.flex-final-button-wrapper .content-wrapper {
    padding-left: 20px;
}

.final-step-warn-item,
#important-final-step-warning {
    border: 1px solid #cdcdcd;
    border-radius:2px;
    padding: 15px;
    color:maroon;
    font-style:italic;
    margin:10px 0 40px 0;
}

#installer-result-title {
    margin-bottom: 5px;
}

table.s4-report-results {
    border-collapse:collapse;
    width: 100%;
    border: 1px solid #DFDFDF;
    box-shadow: 4px 8px 11px -8px rgba(0,0,0,0.41);
}

table.s4-report-results th {
    background-color: #efefef;
    padding: 3px;
    font-weight: bold;
    text-align: center;
    font-size: 14px;
}

table.s4-report-results td {
    padding: 3px; 
    white-space:nowrap; 
    border:1px solid #dfdfdf; 
    text-align:center; 
}

.s4-report-results .badge {
    width: 75px;
}

table.s4-report-results td:first-child {
    text-align: left; 
    font-weight: bold; 
    padding-left: 5px
}

table.s4-final-step {width:100%;}
table.s4-final-step td {padding:5px 15px 5px 5px;font-size:13px; }
table.s4-final-step td:first-child {white-space:nowrap; width:165px}

.final-review-actions {
    font-style: italic;
    color: #333;
    line-height: 20px;
    padding-left: 12px;
}

.final-review-drag-drop-advertisement {
    text-align: center;
}

button.s4-final-btns {
    cursor:pointer; 
    color:#fff; 
    font-size:16px; 
    border-radius:5px; 
    padding:7px; 
    background-color:#13659C; 
    border:1px solid gray; 
    width:145px;
}
button.s4-final-btns:hover {background-color: #dfdfdf;}
div.s4-warn {color:maroon;}

.s4-pro-upsell {
    text-align:center;
    width:100%;
    padding:75px 0 50px 0;
    font-size:16px;
    font-weight:bold;
}

.s4-pro-upsell a{
    color:#197b19 !important;
}

/* ============================
HELP POPUP
============================    */
#page-help #content {
    width: 100%;
    max-width: 1024px;
    margin: 0 auto;
}

.ui-tabs-panel >  .help-target,
.hdr-sub1-area >  .help-target {
    position: absolute;
    top: -1px;
    right: 20px;
}

div.help-target a { 
    font-size:16px; 
    color:#13659C
}

div#main-help sup {font-size:11px; font-weight:normal; font-style:italic; color:blue}
div#main-help code {
    display: block;
    margin:10px 0 10px 0;
    color: maroon;
    background: #faf8f8;
    padding: 10px;
    border: 1px solid #efefef;
}
div.help-online {text-align:center; font-size:18px; line-height:24px}
div.help {color:#555; font-style:italic; font-size:11px; padding:4px; border-top:1px solid #dfdfdf}
div.help-page fieldset {margin-bottom:25px}
div#main-help {font-size:13px; line-height:18px}
div#main-help h3 {border-bottom:1px solid silver; padding:8px 0 8px 0; margin:4px 0 8px 0; font-size:20px}
div#main-help h4 {margin:8px 0 8px 0; font-size:15px}
div#main-help span.step {color:#DB4B38}
.help-opt {width: 100%; border: none; border-collapse: collapse;  margin:5px 0 0 0;}
.help-opt .col-opt {
    width:150px;
}
.help-opt td.section  {background-color:#c7c5c5 !important;}
.help-opt td, .help-opt th {padding:15px 10px; border:1px solid silver;}
.help-opt td:first-child {font-weight:bold; padding-right:10px; white-space:nowrap; text-align:center; background:#efefef}
.help-opt th {background: #333; color: #fff;border:1px solid #333; padding:7px }

#main-help section {
    margin-top: 28px;
    border-radius:2px;
    overflow: hidden;
}

#main-help section h2.header {
    background-color:#F1F1F1;
    padding:15px;
    margin:0;
    font-size:20px;
    user-select:none;
}

#main-help section h2.header sup {
    color:#999;
    font-size:12px;
    font-weight:normal;
    float:right;
    padding:5px 0 0 0;
}

#main-help section .content {
    padding:10px 20px 10px 20px;
}

div#main-help ul li {padding:3px}

/* ============================
Expandable section
============================    */
.expandable.close .expand-header {
    cursor: pointer;
}

.expandable.open .expand-header {
    cursor: pointer;
}

.expandable .expand-header::before {
    font-family: "Font Awesome 5 Free";
    margin-right: 10px;
}

.expandable.close .expand-header::before {
    content: "\f0fe";
}

.expandable.open .expand-header::before {
    content: "\f146";
}

.expandable.close .content {
    display: none;
}

.expandable.open .content {
    display: block;
}

/* ============================
VIEW EXCEPTION
============================    */
.exception-trace {
    overflow: auto;
    border: 1px solid lightgray;
    padding: 10px;
    margin: 0;
}

/*================================================
LIB OVERIDES*/
input.parsley-error, textarea.parsley-error, select.parsley-error {
    color:#B94A48 !important;
    background-color:#F2DEDE !important;
    border:1px solid #EED3D7 !important;
}
ul.parsley-errors-list {margin:1px 0 0 -40px; list-style-type:none; font-size:10px}
.ui-widget {font-size:13px}


<?php if ($GLOBALS['DUPX_DEBUG']) : ?>
    .dupx-debug {display:block; margin:0 0 25px 0; font-size:11px; background-color:#f5dbda; padding:8px; border:1px solid silver; border-radius:2px}
    .dupx-debug label {font-weight:bold; display:block; margin:4px 0 1px 0}
    .dupx-debug textarea {width:95%; height:100px; font-size:11px}
    .dupx-debug input {font-size:11px; padding:3px}
<?php else : ?>
    .dupx-debug {display:none}
<?php endif; ?>

/**PARAMS MANAGER DEBUG **/


#params-html-info {
    position: fixed;
    top: 0;
    left: 940px;
    height: 100vh;
    width: calc(100vw - 940px);
    overflow: auto;
    background-color: rgba(255,255,255,.7);
    white-space: nowrap;
    font-family: monospace;
    font-size: 12px;
    line-height: 2;
    box-sizing: border-box;
    padding: 20px;
    border-left: 1px dotted
}

#installer-mode-content {
    margin-top: 28px;
    padding: 12px;
    border: 1px solid silver;
    border-radius: 4px;
}

#addtional-help-content {
    text-align:center;
    margin-top: 28px;
}

/** SELECT 2 THEME */
.select2-container .select2-selection--single {
    height: 30px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    color: #000;
    line-height: 30px; 
}

.select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
    border-color: transparent transparent #000 transparent;
}

.select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: #000 transparent transparent transparent;
}


/* ============================
FATAL ERROR MESSAGES
============================    */
.more-content {
    overflow: hidden;
    position: relative;
    max-height: 0;
}

.more-content.more::after {
    content: "";
    position: absolute;
    bottom: 0;
    right: 0;
    height: 60px;
    width: 100%;
    background-image: linear-gradient(transparent, rgba(255,255,255,0.95) 70%);
}

.more-content .more-button,
.more-content .all-button {
    position: absolute;
    bottom: 0;
    z-index: 1000;
    display: none;
    background: rgba(255,255,255,0.5);
    border: 0 none;
    padding:0 0 5px 0;
    margin: 0;
    color: #365899;
    cursor: pointer;
}

.more-content .more-button:hover,
.more-content .all-button:hover {
    text-decoration: underline;
}

.more-content .more-button {
    left: 0;
}

.more-content .all-button {
    right: 0;
}

.more-content.more .more-button,
.more-content.more .all-button {
    display: block;
}

div.more-faq-link {
    padding:15px 0 15px 0;
    text-align:center;
    margin-bottom:25px;
}
</style>
<?php
require dirname(__FILE__) . '/inc.css.validation.php';
DUPX_U_Html::css();
