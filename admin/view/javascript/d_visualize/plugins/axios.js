import {getDataOpencart, getUrlOpencart} from '~/utils';

// process.env.NODE_TLS_REJECT_UNAUTHORIZED = '0';

export default ({$axios, store,redirect}) => {

    if (process.server) {
        return;
    }
    // Request interceptor
    $axios.interceptors.request.use(request => {
        // store.commit('load/LOADING_START');
        if (request.url === 'shopunity') {
            var parsed_url = new URL(request.baseURL);
                let url  =`${parsed_url.origin}${parsed_url.pathname}v1/extensions?type=theme&tag=d_visualize`;
            request.url = '';
            request.baseURL = url
        } else {
            if (!process.env.isDev) {
                request.baseURL = window.location.origin + window.location.pathname;
            }
            let url = getUrlOpencart(request.baseURL, process.env.isDev);
            url.route = request.url;
            if (process.env.isDev) {
                url.user_token = 'user_token=' + process.env.user_token + '&token=' + process.env.user_token;
            }
            request.baseURL = url.makeUrl();
            request.url = '';
            if (request.method === 'post') {
                request.transformRequest = (data, header) => {
                    return getDataOpencart(data, header)
                }
            }
        }
        return request;
    }, (err) => {
        console.log(err);
        return Promise.reject(error);
    });
    $axios.interceptors.response.use(response => {
        if (response.data.redirect){
            // redirect(response.data.redirect)
        }
        store.dispatch('error/LOAD_RESPONSE',response);
        return response;
    }, (err) => {
        store.dispatch('error/LOAD_RESPONSE',response);
        return Promise.reject(err);
    });
}
