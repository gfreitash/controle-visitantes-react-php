import {axiosPrivate} from '../api/axios';
import useRefreshToken from './useRefreshToken';
import useAuth from './useAuth';
import {useEffect} from 'react';

export default function useAxiosPrivate() {
    const {auth} = useAuth();
    const refresh = useRefreshToken();

    useEffect(() => {
        const interceptadorRequisicao = axiosPrivate.interceptors.request.use(
            config => {
                if(!config.headers['Authorization']) {
                    config.headers['Authorization'] = auth?.accessToken;
                }
                return config;
            }, error => {
                return Promise.reject(error);
            }
        );

        const interceptadorResposta = axiosPrivate.interceptors.response.use(
            response => response,
            async error => {
                const prevRequest = error?.config;
                if (error.response.status === 401 && !prevRequest?.sent) {
                    prevRequest.sent = true;
                    const accessToken = await refresh();
                    prevRequest.headers.Authorization = `Bearer ${accessToken}`;
                    return axiosPrivate(prevRequest);
                }
                const alerta = {mensagem: "Sua sessão expirou.", tipo: "danger"};
                return Promise.reject(error);
            }
        );

        return () => {
            axiosPrivate.interceptors.request.eject(interceptadorRequisicao);
            axiosPrivate.interceptors.response.eject(interceptadorResposta);
        }
    }, [auth, refresh]);

    return axiosPrivate;
}
