export var baseService;
export default (baseService = {
  handleResponse,
  handleUpdateResponse
});

function handleResponse(response) {
  return response.text().then(text => {
    const body = text && JSON.parse(text);
    if (!response.ok) {
      if (response.status === 403) {
        // auto logout if 401 response returned from api
        //TODO: auto logout
      }
      const message = (body && body.message) || response.statusText;
      return Promise.reject({ status: response.status, message: message });
    }

    if (!body.status) {
      const message = body.message;
      return Promise.reject({ status: response.status, message: message });
    }
    return body.data;
  });
}

function handleUpdateResponse(response) {
  return response.text().then(text => {
    const body = text && JSON.parse(text);
    if (!response.ok) {
      if (response.status === 403) {
        // auto logout if 401 response returned from api
        //TODO: auto logout
      }
      const message = (body && body.message) || response.statusText;
      return Promise.reject({ status: response.status, message: message });
    }

    if (!body.status) {
      const message = body.message;
      return Promise.reject({ status: response.status, message: message });
    }
    return body;
  });
}