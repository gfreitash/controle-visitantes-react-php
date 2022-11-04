import {axiosPrivate} from '../api/axios';
import useRefreshToken from './useRefreshToken';
import useAuth from './useAuth';
import {useEffect} from 'react';
import useInvalidSessionHandler from "./useInvalidSessionHandler";

export default function useAxiosPrivate() {
    const {auth} = useAuth();
    const refresh = useRefreshToken();
    const handleInvalidSession = useInvalidSessionHandler();

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
                const prevRequest = error.config;
                if (error.code !== "ERR_CANCELED" && error.response?.status === 401 && !prevRequest?.sent) {
                    try {
                        prevRequest.sent = true;
                        const accessToken = await refresh();
                        prevRequest.headers.Authorization = `Bearer ${accessToken}`;
                        return axiosPrivate(prevRequest);
                    } catch (error) {
                        if (error.response?.status === 401) {
                            handleInvalidSession();
                        }
                    }
                }

                return Promise.reject(error);
            }
        );

        return () => {
            axiosPrivate.interceptors.request.eject(interceptadorRequisicao);
            axiosPrivate.interceptors.response.eject(interceptadorResposta);
        }
    }, [auth]);

    return axiosPrivate;
}
