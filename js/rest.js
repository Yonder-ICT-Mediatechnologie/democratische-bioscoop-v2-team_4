/**
 * Read films from remoteserver
 * @param remoteserver
 * @param apiKey
 * @returns {Promise}
 */
const getFilms = async (remoteServer, apiKey) => {
  const response = await fetch(`${remoteServer}/list/${apiKey}`);
  return response.json();
};

/**
 *
 * @param remoteserver
 * @param apiKey
 * @param idFilm
 * @returns {Promise<any>}
 */
const getFilmDetails = async (remoteServer, apiKey, idFilm) => {
  const response = await fetch(`${remoteServer}/details/${idFilm}/${apiKey}`, {
    method: 'GET',
    mode: 'cors',
    cache: 'no-cache',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json'
    },
  });
  return response.json();
};

/**
 *
 * @param remoteServer
 * @param apiKey
 * @param data
 * @returns {Promise<any>}
 */
const postFilmData = async (remoteServer, apiKey, data = {}) => {
  data.apikey = apiKey;
  const response = await fetch(`${remoteServer}/add/${apiKey}`, {
    method: 'POST',
    mode: 'cors',
    cache: 'no-cache',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data) // body data type must match "Content-Type" header
  });
  return response.json(); // parses JSON response into native JavaScript objects
}

/**
 *
 * @param remoteServer
 * @param apiKey
 * @param idFilm
 * @returns {Promise<any>}
 */
const voteUp = async (remoteServer,apiKey, idFilm) => {
  const response = await fetch(`${remoteServer}/voteup/${idFilm}/${apiKey}`, {
    method: 'PUT',
    mode: 'cors',
    cache: 'no-cache',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({}) // body data type must match "Content-Type" header
  });
  return response.json(); // parses JSON response into native JavaScript objects
}

/**
 *
 * @param remoteServer
 * @param apiKey
 * @param idFilm
 * @returns {Promise<any>}
 */
const deleteFilm = async (remoteServer,apiKey, idFilm) => {
  const response = await fetch(`${remoteServer}/delete/${idFilm}/${apiKey}`, {
    method: 'DELETE',
    mode: 'cors',
    cache: 'no-cache',
    credentials: 'same-origin',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({}) // body data type must match "Content-Type" header
  });
  return response.json(); // parses JSON response into native JavaScript objects
}