let RestClient = {
    get: function (url, callback, error_callback) {
        $.ajax({
            url: Constants.PROJECT_BASE_URL + "/" + url,
            type: "GET",
            beforeSend: function (xhr) {
                const token = localStorage.getItem("user_token");
                if (token) {
                    xhr.setRequestHeader("Authentication", token);
                    xhr.setRequestHeader("Authorization", "Bearer " + token);
                }
            },
            success: function (response) {
                if (callback) callback(response);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("GET Error:", jqXHR.status, jqXHR.responseText);
                if (error_callback) {
                    error_callback(jqXHR);
                } else {
                    let errorMessage = 'Request failed';
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        errorMessage = jqXHR.responseJSON.message;
                    } else if (jqXHR.responseText) {
                        errorMessage = jqXHR.responseText;
                    }
                    toastr.error(errorMessage);
                }
            },
        });
    },
    
    request: function (url, method, data, callback, error_callback) {
        console.log(`Making ${method} request to:`, Constants.PROJECT_BASE_URL + "/" + url);
        
        $.ajax({
            url: Constants.PROJECT_BASE_URL + "/" + url,
            type: method,
            data: JSON.stringify(data),
            contentType: "application/json",
            dataType: "json",
            beforeSend: function (xhr) {
                const token = localStorage.getItem("user_token");
                if (token) {
                    xhr.setRequestHeader("Authentication", token);
                    xhr.setRequestHeader("Authorization", "Bearer " + token);
                    console.log("Token being sent:", token.substring(0, 20) + "...");
                }
            },
        })
        .done(function (response, status, jqXHR) {
            if (callback) callback(response);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error(`${method} Error:`, jqXHR.status, jqXHR.responseText);
            if (error_callback) {
                error_callback(jqXHR);
            } else {
                let errorMessage = 'Request failed';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (jqXHR.responseText) {
                    errorMessage = jqXHR.responseText;
                }
                toastr.error(errorMessage);
            }
        });
    },
    
    post: function (url, data, callback, error_callback) {
        RestClient.request(url, "POST", data, callback, error_callback);
    },
    
    delete: function (url, data, callback, error_callback) {
        console.log("Making DELETE request to:", Constants.PROJECT_BASE_URL + "/" + url);
        
        $.ajax({
            url: Constants.PROJECT_BASE_URL + "/" + url,
            type: "DELETE",
            beforeSend: function (xhr) {
                const token = localStorage.getItem("user_token");
                if (token) {
                    xhr.setRequestHeader("Authentication", token);
                    xhr.setRequestHeader("Authorization", "Bearer " + token);
                    console.log("Token being sent:", token.substring(0, 20) + "...");
                }
            },
        })
        .done(function (response, status, jqXHR) {
            if (callback) callback(response);
        })
        .fail(function (jqXHR, textStatus, errorThrown) {
            console.error("DELETE Error:", jqXHR.status, jqXHR.responseText);
            if (error_callback) {
                error_callback(jqXHR);
            } else {
                let errorMessage = 'Delete failed';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (jqXHR.responseText) {
                    errorMessage = jqXHR.responseText;
                }
                toastr.error(errorMessage);
            }
        });
    },
    
    patch: function (url, data, callback, error_callback) {
        RestClient.request(url, "PATCH", data, callback, error_callback);
    },
    
    put: function (url, data, callback, error_callback) {
        RestClient.request(url, "PUT", data, callback, error_callback);
    },
};