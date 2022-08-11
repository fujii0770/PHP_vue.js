import config from "../app.config";
import Axios from "axios";
import store from '../store/store';


export var protalService;
export default (protalService = {
    getListServiceInternal,
    addFavorite,
    getListFavorite,
    deleteFavorite,
    getMyPages,
    saveMyPage,
    updateMyPage,
    deleteMyPage,
    getMyPageLayout,
    getTopicList,
    getFaqTopicList,
    getBbsCategories,
    getFaqBbsCategories,
    getBbsAuth,
    getBbsMember,
    getBbsMemberForPage,
    getBbsMemberListByIds,
    deleteBbsTopic,
    deleteFaqBbsTopic,
    deleteBbsComment,
    deleteFaqBbsComment,
    deleteBbsCategory,
    updateBbsTopic,
    updateFaqBbsTopic,
    updateBbsComment,
    updateFaqBbsComment,
    updateBbsCategory,
    addBbsTopic,
    addFaqBbsTopic,
    addBbsComment,
    addFaqBbsComment,
    addBbsCategory,
    getBbsTopicLikes,
    addBbsTopicLike,
    deleteBbsTopicLike,
    addBbsDraftTopic,
    updateBbsDraftTopic,
    deleteBbsDraftTopic,
    timeCardStore,
    timeCardUpdate,
    lastPunched,
    timeCardList,
    timeCardShowDetail,
    timeCardDownloadCSV,
    reserveBbsAttachment,
    getToDoList,
    getToDoListDetail,
    addToDoList,
    updateToDoList,
    deleteToDoList,
    getToDoTask,
    getToDoTaskDetail,
    addToDoTask,
    updateToDoTask,
    deleteToDoTask,
    doneToDoTask,
    revokeToDoTask,
    getToDoCircular,
    getToDoCircularDetail,
    updateToDoCircularTask,
    getToDoPublicSchedulerList,
    getToDoGroup,
    getToDoGroupList,
    getToDoGroupDetail,
    getToDoDepartment,
    getToDoUsers,
    addToDoGroup,
    updateToDoGroup,
    deleteToDoGroup,
    settingToDoNotice,
    getToDoNoticeConfig,
    countUnreadToDoNotice,
    getUnReadToDoNotice,
    readToDoNotice,
    readAllToDoNotice,
});

function getListFavorite(mypage_id) {
    return Axios.get(`${config.BASE_API_URL}/favorite?mypage_id=${mypage_id}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getListServiceInternal() {
    return Axios.get(`${config.BASE_API_URL}/internalsv`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function addFavorite(data) {
    return Axios.post(`${config.BASE_API_URL}/favorite`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function deleteFavorite(id) {
    return Axios.delete(`${config.BASE_API_URL}/favorite/${id}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getMyPages() {
    return Axios.get(`${config.BASE_API_URL}/mypage`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function saveMyPage(info) {
    return Axios.post(`${config.BASE_API_URL}/mypage`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function updateMyPage(info) {
    return Axios.put(`${config.BASE_API_URL}/mypage/${info.id}`,info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function deleteMyPage(id) {
    return Axios.delete(`${config.BASE_API_URL}/mypage/${id}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getMyPageLayout() {
    return Axios.get(`${config.BASE_API_URL}/mstmypage`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}
function getTopicList(data)
{
    return Axios.get(`${config.BASE_API_URL}/bbslist`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function getFaqTopicList(data)
{
    return Axios.get(`${config.BASE_API_URL}/faqbbslist`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function getBbsCategories(data)
{
    return Axios.get(`${config.BASE_API_URL}/bbscategorylist`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function getFaqBbsCategories(data)
{
    return Axios.get(`${config.BASE_API_URL}/faqbbscategorylist`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function getBbsAuth()
{
    return Axios.get(`${config.BASE_API_URL}/bbsAuth`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function getBbsMember()
{
    return Axios.get(`${config.BASE_API_URL}/bbsMember`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function getBbsMemberForPage(page, search)
{
    return Axios.get(`${config.BASE_API_URL}/bbsMemberForPage?page=` + page + '&search=' + search)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}
function getBbsMemberListByIds(ids)
{
    return Axios.get(`${config.BASE_API_URL}/bbsMemberByIds?ids=` + ids)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}
function deleteBbsTopic(data)
{
    return Axios.post(`${config.BASE_API_URL}/delbbstopic`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function deleteFaqBbsTopic(data)
{
    return Axios.post(`${config.BASE_API_URL}/delfaqbbstopic`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function deleteBbsComment(data)
{
    return Axios.post(`${config.BASE_API_URL}/delbbscomment`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function deleteFaqBbsComment(data)
{
    return Axios.post(`${config.BASE_API_URL}/delfaqbbscomment`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function deleteBbsCategory(data)
{
    return Axios.post(`${config.BASE_API_URL}/delbbscategory`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function updateBbsTopic(data)
{
    return Axios.post(`${config.BASE_API_URL}/updbbstopic`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function updateFaqBbsTopic(data)
{
    return Axios.post(`${config.BASE_API_URL}/updfaqbbstopic`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function updateBbsComment(data)
{
    return Axios.post(`${config.BASE_API_URL}/updbbscomment`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function updateFaqBbsComment(data)
{
    return Axios.post(`${config.BASE_API_URL}/updfaqbbscomment`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function updateBbsCategory(data)
{
    return Axios.post(`${config.BASE_API_URL}/updbbscategory`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function addBbsTopic(data)
{
    return Axios.post(`${config.BASE_API_URL}/addbbstopic`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function addFaqBbsTopic(data)
{
    return Axios.post(`${config.BASE_API_URL}/addfaqbbstopic`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function addBbsComment(data)
{
    return Axios.post(`${config.BASE_API_URL}/addbbscomment`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function addFaqBbsComment(data)
{
    return Axios.post(`${config.BASE_API_URL}/addfaqbbscomment`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function addBbsCategory(data)
{
    return Axios.post(`${config.BASE_API_URL}/addbbscategory`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });

}
function getBbsTopicLikes(data)
{
    return Axios.get(`${config.BASE_API_URL}/getBbsTopicLikes`, {params: data})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
    
}
function addBbsTopicLike(data)
{
    return Axios.post(`${config.BASE_API_URL}/addBbsTopicLike`, data)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
    
}
function deleteBbsTopicLike(data)
{
    return Axios.post(`${config.BASE_API_URL}/deleteBbsTopicLike`, data)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
    
}

function addBbsDraftTopic(data)
{
    return Axios.post(`${config.BASE_API_URL}/addBbsDraftTopic`, data)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}
function updateBbsDraftTopic(data)
{
    return Axios.post(`${config.BASE_API_URL}/updateBbsDraftTopic`, data)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}
function deleteBbsDraftTopic(data)
{
    return Axios.post(`${config.BASE_API_URL}/delBbsDraftTopic`, data)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}
function timeCardStore(data)
{
    return Axios.post(`${config.BASE_API_URL}/timecard/${data}`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function timeCardUpdate(data)
{
    return Axios.put(`${config.BASE_API_URL}/timecard/update`, data)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function lastPunched()
{
    return Axios.get(`${config.BASE_API_URL}/timecard`)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function timeCardList(data)
{
    return Axios.get(`${config.BASE_API_URL}/timecard/search-list`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function timeCardShowDetail(data)
{
    return Axios.get(`${config.BASE_API_URL}/timecard/show-detail`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function timeCardDownloadCSV(data)
{
    return Axios.get(`${config.BASE_API_URL}/timecard/csvDownload`, {params: data})
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function reserveBbsAttachment(info) {
    return Axios.post(`${config.BASE_API_URL}/bbsAttachment/reserve`, info)
        .then(response => {
            return Promise.resolve(response.data);
        })
        .catch(error => {
            error = error.response;
            const message = (error && error.data && error.data.message) || error.statusText;
            return Promise.reject(message);
        });
}

function getToDoList(data)
{
    return Axios.get(`${config.BASE_API_URL}/to-do-list/list`, {params: data})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
    
}

function getToDoListDetail(data)
{
    return Axios.get(`${config.BASE_API_URL}/to-do-list/list/${data.id}?type=${data.type}`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
    
}

function addToDoList(data)
{
    return Axios.post(`${config.BASE_API_URL}/to-do-list/list`, data)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
    
}

function updateToDoList(data)
{
    return Axios.put(`${config.BASE_API_URL}/to-do-list/list/${data.id}`, data)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
    
}

function deleteToDoList(data)
{
    let headers = {
        'Content-Type': 'multipart/form-data'
    };
    headers['gwauthorization'] = `${data.tokenGroupware}`;
    headers['Pragma'] = `no-cache`;
    headers['Cache-Control'] = `no-cache,no-store`;
    delete data.tokenGroupware;
    return Axios.delete(`${config.BASE_API_URL}/to-do-list/list/${data.id}`, {headers: headers})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
    
}

function getToDoTask(data)
{
    let to_do_id = data.to_do_id;
    delete data.to_do_id;
    return Axios.get(`${config.BASE_API_URL}/to-do-list/${to_do_id}/task`, {params: data})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getToDoTaskDetail(data)
{
    let headers = {
        'gwauthorization': `${data.tokenGroupware}`
    };
    delete data.tokenGroupware;
    return Axios.get(`${config.BASE_API_URL}/to-do-list/task/${data.id}`, {headers: headers})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function addToDoTask(data)
{
    let headers = {
        'Content-Type': 'multipart/form-data'
    };
    headers['gwauthorization'] = `${data.tokenGroupware}`;
    headers['Pragma'] = `no-cache`;
    headers['Cache-Control'] = `no-cache,no-store`;
    delete data.tokenGroupware;
    return Axios.post(`${config.BASE_API_URL}/to-do-list/task`, data, {headers: headers})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function updateToDoTask(data)
{
    let headers = {
        'Content-Type': 'multipart/form-data'
    };
    headers['gwauthorization'] = `${data.tokenGroupware}`;
    headers['Pragma'] = `no-cache`;
    headers['Cache-Control'] = `no-cache,no-store`;
    delete data.tokenGroupware;
    return Axios.put(`${config.BASE_API_URL}/to-do-list/task/${data.id}`, data, {headers: headers})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
    
}

function deleteToDoTask(data)
{
    let headers = {
        'Content-Type': 'multipart/form-data'
    };
    headers['gwauthorization'] = `${data.tokenGroupware}`;
    headers['Pragma'] = `no-cache`;
    headers['Cache-Control'] = `no-cache,no-store`;
    delete data.tokenGroupware;
    return Axios.delete(`${config.BASE_API_URL}/to-do-list/task/${data.id}`, {headers: headers})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function doneToDoTask(task_id)
{
    return Axios.post(`${config.BASE_API_URL}/to-do-list/task/done/${task_id}`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function revokeToDoTask(task_id)
{
    return Axios.post(`${config.BASE_API_URL}/to-do-list/task/revoke/${task_id}`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getToDoCircular(data)
{
    return Axios.get(`${config.BASE_API_URL}/to-do-list/circulars`, {params: data})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getToDoCircularDetail(data)
{
    let headers = {
        'gwauthorization': `${data.tokenGroupware}`
    };
    delete data.tokenGroupware;
    return Axios.get(`${config.BASE_API_URL}/to-do-list/circular/${data.circular_user_id}`, {headers: headers})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function updateToDoCircularTask(data)
{
    let headers = {
        'Content-Type': 'multipart/form-data'
    };
    headers['gwauthorization'] = `${data.tokenGroupware}`;
    headers['Pragma'] = `no-cache`;
    headers['Cache-Control'] = `no-cache,no-store`;
    delete data.tokenGroupware;
    return Axios.put(`${config.BASE_API_URL}/to-do-list/circular/${data.circular_user_id}`, data, {headers: headers})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getToDoPublicSchedulerList(data)
{
    let headers = {
        'gwauthorization': `${data.tokenGroupware}`
    };
    delete data.tokenGroupware;
    return Axios.get(`${config.BASE_API_URL}/to-do-list/scheduler?type=${data.type}&shared=${data.shared}`, {headers: headers})
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getToDoGroup()
{
    return Axios.get(`${config.BASE_API_URL}/to-do-list/group`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getToDoGroupList()
{
    return Axios.get(`${config.BASE_API_URL}/to-do-list/group-list`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getToDoGroupDetail(id)
{
    return Axios.get(`${config.BASE_API_URL}/to-do-list/group/${id}`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getToDoDepartment()
{
    return Axios.get(`${config.BASE_API_URL}/to-do-list/department`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getToDoUsers()
{
    return Axios.get(`${config.BASE_API_URL}/to-do-list/users`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function addToDoGroup(data)
{
    return Axios.post(`${config.BASE_API_URL}/to-do-list/group`, data)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function updateToDoGroup(data)
{
    return Axios.put(`${config.BASE_API_URL}/to-do-list/group/${data.id}`, data)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function deleteToDoGroup(id)
{
    return Axios.delete(`${config.BASE_API_URL}/to-do-list/group/${id}`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function settingToDoNotice(data)
{
    return Axios.put(`${config.BASE_API_URL}/to-do-list/notice`, data)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getToDoNoticeConfig()
{
    return Axios.get(`${config.BASE_API_URL}/to-do-list/notice`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function countUnreadToDoNotice()
{
    return Axios.get(`${config.BASE_API_URL}/to-do-list/unread/count`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function getUnReadToDoNotice()
{
    return Axios.get(`${config.BASE_API_URL}/to-do-list/unread`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function readToDoNotice(id)
{
    return Axios.post(`${config.BASE_API_URL}/to-do-list/read/${id}`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}

function readAllToDoNotice()
{
    return Axios.post(`${config.BASE_API_URL}/to-do-list/read-all`)
    .then(response => {
        return Promise.resolve(response.data);
    })
    .catch(error => {
        error = error.response;
        const message = (error && error.data && error.data.message) || error.statusText;
        return Promise.reject(message);
    });
}