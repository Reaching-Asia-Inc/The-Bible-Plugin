import {atom} from 'nanostores';

function queryParam(paramName, fallbackValue = '') {
    const searchParams = new URLSearchParams(window.location.search);
    let initialValue = fallbackValue;
    if (searchParams.has(paramName)) {
        initialValue = searchParams.get(paramName);
    }
    const $store = atom(initialValue);

    $store.subscribe((value, oldValue) => {
        updateUrl(paramName, value); // Update URL based on new value
    })

    function updateUrl(paramName, paramValue) {
        const searchParams = new URLSearchParams(window.location.search);
        const current = searchParams.get(paramName);

        if (!paramValue) {
            if (current !== null) {
                searchParams.delete(paramName);
                history.pushState({}, '', `${window.location.origin}${window.location.pathname}?${searchParams.toString()}`);
            }
            return;
        }

        if (current !== paramValue) {
            searchParams.set(paramName, paramValue);
            history.pushState({}, '', `${window.location.origin}${window.location.pathname}?${searchParams.toString()}`);
        }
    }

    function updateFromQuery() {
        const searchParams = new URLSearchParams(window.location.search);
        $store.set(searchParams.get(paramName) || fallbackValue);
    }

    updateFromQuery(); // Call initially to set from query string
    window.onpopstate = updateFromQuery;

    return $store;
}

export {queryParam};
