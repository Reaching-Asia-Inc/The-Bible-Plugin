import {nanoquery} from '@nanostores/query';

export const [fetchState, mutateResponse] = nanoquery({
    fetcher: (...keys) => {
        //If any of the keys are undefined or null, return pending
        if (keys.some(k => k === undefined || k === null)) {
            return new Promise(() => {
            });
        }
      return fetch(keys.join(''), {
        headers: {
          'Accept': 'application/json'
        }
      })
        .then((r) => {
            if (!r.ok) {
                return r.json().then(err => { throw err; });
            }
            return r.json();
        })
    },
});
