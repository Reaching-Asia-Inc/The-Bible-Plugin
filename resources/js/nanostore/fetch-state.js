import {nanoquery} from '@nanostores/query';

export const [fetchState, mutateResponse] = nanoquery({
    fetcher: (...keys) => {
        //If any of the keys are empty, return pending
        if (keys.some(k => !k)) {
            return new Promise(() => {
            });
        }
      return fetch(keys.join(''), {
        headers: {
          'Accept': 'application/json'
        }
      })
        .then((r) => r.json())
        .catch((r) => r.json())
    },
});
