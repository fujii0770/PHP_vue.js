import config from "../app.config";
import Axios from "axios";

export var expenseSettlement;

export default (expenseSettlement = {
    getActuarialData,
    getMFormPurposeDataSelect,
    getEpsMPurposeInfo,
    getCurrentUserDepartmentInfo,
    getListTAppItems,
    getMPurposeDataSelect,
    saveExpense,
    updateExpense,
    updateEpsTAppItem,
    duplicateEpsTAppItem,
    duplicateEpsTAppAndEpsTAppItems,
    createEpsTAppItem,
    getEpsMWtsmName,
    getEpsTAppItemDetail,
    createFormSettlementFromAdvanceForm,
    getEpsMFormRelation,
    deleteEpsTAppAndItems,
    displayAndValidatePrice,
    downloadFile,
    deleteFile,
    updateExpenseFormInput,
    saveExpenseInputData,
    updateExpenseCircularInfo,
    getCircularSentById,
});

function getActuarialData(data) {
    return Axios.get(`${config.BASE_API_URL}/expense_settlement`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getEpsTAppItemDetail(data) {
    return Axios.get(`${config.BASE_API_URL}/expense_settlement/getEpsTAppItemDetail/${data.id}`,
        {params: data.data_params})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getEpsMPurposeInfo(data) {
    return Axios.get(`${config.BASE_API_URL}/expense_settlement/getEpsMPurposeInfo`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getMFormPurposeDataSelect(data) {
    return Axios.get(`${config.BASE_API_URL}/expense_settlement/getMFormPurposeDataSelect`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getCurrentUserDepartmentInfo(data) {
    return Axios.get(`${config.BASE_API_URL}/expense_settlement/getCurrentUserDepartmentInfo`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getListTAppItems(data) {
    return Axios.get(`${config.BASE_API_URL}/expense_settlement/getListTAppItems`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getMPurposeDataSelect(data) {
    return Axios.get(`${config.BASE_API_URL}/expense_settlement/getMPurposeDataSelect`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function saveExpense(data) {
    return Axios.post(`${config.BASE_API_URL}/expense_settlement/saveExpense`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateExpense(data) {
    let id = data.get('id')
    if (id) {
        return Axios.post(`${config.BASE_API_URL}/expense_settlement/updateExpense/${id}`, data)
            .then(response => {
                return Promise.resolve(response.data);
            })
            .catch(error => {
                error = error.response;
                const message = (error && error.data && error.data.message) || error.statusText;
                return Promise.reject(message);
            });
    }
}

function updateEpsTAppItem(data) {
    return Axios.put(`${config.BASE_API_URL}/expense_settlement/updateEpsTAppItem/${data.id}`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function duplicateEpsTAppItem(data) {
    return Axios.put(`${config.BASE_API_URL}/expense_settlement/duplicateEpsTAppItem/${data.id}`, data.data_params)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function duplicateEpsTAppAndEpsTAppItems(data) {
    return Axios.put(`${config.BASE_API_URL}/expense_settlement/duplicateEpsTAppAndEpsTAppItems/${data.id}`, data.data_params)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function createFormSettlementFromAdvanceForm(data) {
    return Axios.put(`${config.BASE_API_URL}/expense_settlement/createFormSettlementFromAdvanceForm/${data.id}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function createEpsTAppItem(data) {
    return Axios.post(`${config.BASE_API_URL}/expense_settlement/createEpsTAppItem`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getEpsMWtsmName(data) {
    return Axios.get(`${config.BASE_API_URL}/expense_settlement/getEpsMWtsmName`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getEpsMFormRelation(data) {
    return Axios.get(`${config.BASE_API_URL}/expense_settlement/getEpsMFormRelation`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function deleteEpsTAppAndItems(data) {
    return Axios.put(`${config.BASE_API_URL}/expense_settlement/deleteEpsTAppAndItems/${data.id}`, data.data_update)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function displayAndValidatePrice(data) {
    return Axios.get(`${config.BASE_API_URL}/expense_settlement/displayAndValidatePrice/${data.id}`,
        {params: data.data_params})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateExpenseFormInput(data) {
    if (data.form_code) {
        return Axios.post(`${config.BASE_API_URL}/expense_settlement/updateExpenseFormInput`, data)
            .then(response => {
                return Promise.resolve(response.data);
            })
            .catch(error => {
                error = error.response;
                const message = (error && error.data && error.data.message) || error.statusText;
                return Promise.reject(message);
            });
    }

}

function saveExpenseInputData(data) {
    return Axios.post(`${config.BASE_API_URL}/expense_settlement/saveExpenseInputData`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function updateExpenseCircularInfo(data) {
    return Axios.post(`${config.BASE_API_URL}/expense_settlement/updateExpenseCircularInfo`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function getCircularSentById(data) {
    return Axios.get(`${config.BASE_API_URL}/circulars/sent/${data.circular_id}`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}

async function downloadFile(fileId) {
    return Axios.get(`${config.BASE_API_URL}/expense_settlement/files/${fileId}`)
}

async function deleteFile(fileId) {
    return Axios.delete(`${config.BASE_API_URL}/expense_settlement/files/${fileId}`)
}
