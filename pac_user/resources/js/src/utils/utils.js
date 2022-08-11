import config from "../app.config";
import { EXPENSE } from '../enums/expense';

function buildTabColorAndLogo(files, companyLogos, currentCompanyId, currentEdition, currentEnv) {
    if (files && files.length > 0) {
        for(let file of files) {
            file.tabColor = "";
            file.tabLogo = "";
            if (file.hasOwnProperty('total_timestamp') && file.total_timestamp){
                // clock logo
                file.timestampLogo = true;
            }else{
                file.timestampLogo = false;
            }
        }
    }
    // remove PAC_5-91
    /*if (files && files.length > 0) {
        let _files = [];
        for(let file of files) {
            _files.push(file);
        }
        _files = _files
            .sort(function (a, b) {
                return a.circular_document_id - b.circular_document_id;
            })
            .map(function (item) {
                item.tabColor = "";
                item.tabLogo = "";
                return item;
            });
        if (_files && _files.length > 0) {
            var companyNo = 1;
            for (let i = 0; i < _files.length; i++) {
                if (_files[i].origin_edition_flg == config.APP_EDITION_FLV && _files[i].origin_env_flg == config.APP_SERVER_ENV && companyLogos && companyLogos.hasOwnProperty(_files[i].mst_company_id.toString())) {
                    _files[i].tabLogo = companyLogos[_files[i].mst_company_id.toString()].logo_file_data;
                }

                if (i > 0 && (_files[i-1].mst_company_id != _files[i].mst_company_id
                    || _files[i-1].origin_edition_flg != _files[i].origin_edition_flg
                    || _files[i-1].origin_env_flg != _files[i].origin_env_flg)) {
                    if (currentCompanyId != _files[i-1].mst_company_id
                        || currentEdition != _files[i-1].origin_edition_flg
                        || currentEnv != _files[i-1].origin_env_flg){
                        companyNo++;
                    }
                }
                if (currentCompanyId != _files[i].mst_company_id
                    || currentEdition != _files[i].origin_edition_flg
                    || currentEnv != _files[i].origin_env_flg){
                    if (companyNo%3 === 1) {
                        _files[i].tabColor = "first";
                    } else if (companyNo%3 === 2) {
                        _files[i].tabColor = "second";
                    } else {
                        _files[i].tabColor = "third";
                    }
                }
            }
        }
    }*/
}
function setCookie(name,value,days) {
    let expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
}
function getCookie(name) {
    let nameEQ = name + "=";
    let ca = document.cookie.split(';');
    for(let i=0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0)==' ') c = c.substring(1,c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
    }
    return null;
}
function setEmailTemplateOptions(info){
    let comment =  [
        info.comment1,
        info.comment2,
        info.comment3,
        info.comment4,
        info.comment5,
        info.comment6,
        info.comment7,
    ];
    return comment;
}
function filterFormatDate(str,type = 0) {
    let date = new Date(str)
    let y = date.getFullYear();
    let m = (date.getMonth()+1 + '').padStart(2,'0');
    let d = (date.getDate() + '').padStart(2,'0');
    let hh = (date.getHours() + '').padStart(2,'0')
    let mm = (date.getMinutes() + '').padStart(2,'0')
    let ss = (date.getSeconds() + '').padStart(2,'0')
    let time;
    switch (type) {
        case 0:
            time = `${y}-${m}-${d}`;
            break;
        case 1:
            time = `${y}-${m}-${d} ${hh}:${mm}:${ss}`;
            break;
        case 2:
            time = `${m}-${d} ${hh}:${mm}:${ss}`;
            break;
        case 3:
            time = `${m}-${d}`;
            break;
        case 4:
            time = `${y}/${m}/${d}`;
            break;
        case 5:
            time = `${y}/${m}/${d} ${hh}:${mm}:${ss}`;
            break;
        case 6:
            time = `${m}/${d} ${hh}:${mm}:${ss}`;
            break;
        case 7:
            time = `${m}/${d}`;
            break;
    }
    return time;
}
function filterNum(num){
    num= (num+'').replace(/,/g,"");
    num = num.split(".");
    var arr = num[0].split("").reverse();
    var res = [];
    for (var i = 0, len = arr.length; i < len; i++) {
        if (i % 3 === 0 && i !== 0) {
            res.push(",");
        }
        res.push(arr[i]);
    }
    res.reverse();
    if (num[1]) {
        res = res.join("").concat("." + num[1]);
    } else {
        res = res.join("");
    }
    const regexp=/(?:\.0*|(\.\d+?)0+)$/
    return res.replace(regexp,'$1')
}

function getUrlByFormType(data){
    let url
    if (data.hasOwnProperty('form_type')) {
        if (data.form_type === EXPENSE.M_FORM_FORM_TYPE_ADVANCE) {
            url = 'expense-form'
        } else {
            url = 'rebate-form'
        }
    }
    return url
}

function formatPrice(value) {
    let val = (value/1).toFixed(0).replace('.', ',')
    return val.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",")
}

export default{
    buildTabColorAndLogo,
    setCookie,
    getCookie,
    setEmailTemplateOptions,
    filterFormatDate,
    getUrlByFormType,
    formatPrice,
    filterNum
}
