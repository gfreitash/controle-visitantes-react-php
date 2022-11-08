import axios from 'axios';

const PROTOCOLO = process.env.REACT_APP_DOMAIN_PROTOCOL;
const HOST = process.env.REACT_APP_DOMAIN_HOST;
const PORTA = process.env.REACT_APP_BACKEND_PORT;
const BASE_URL = `${PROTOCOLO}://${HOST}:${PORTA}`;

export default axios.create({
    baseURL: BASE_URL
});

export const axiosPrivate = axios.create({
    baseURL: BASE_URL,
    withCredentials: true
});
