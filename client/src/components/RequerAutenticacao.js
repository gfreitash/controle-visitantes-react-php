import {Outlet} from "react-router-dom";
import useAuth from "../hooks/useAuth";
import {useEffect} from "react";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";
import axios from "../api/axios";


export default function RequerAutenticacao() {
    const {auth, setAuth} = useAuth();
    const handleInvalidSession = useInvalidSessionHandler();
    let recarregado = false;

    useEffect(() => {
        const controller = new AbortController();

        if(!auth?.accessToken && !recarregado) {
            const getAuth = async () => {
                try {
                    const response = await axios.get("/refresh", {
                        sinal: controller.signal,
                        withCredentials: true
                    });

                    const accessToken = response?.data?.accessToken ? "Bearer " + response?.data?.accessToken : null;
                    const nome = response?.data?.nome;
                    const id = response?.data?.id;
                    const email = response?.data?.email;
                    const refreshed = true;

                    setAuth({nome, id, email, accessToken, refreshed});
                } catch (e) {
                    handleInvalidSession();
                }
            }
            getAuth();
        }
        return () => {
            recarregado = true;
            controller.abort();
        }
    }, []);

    return (<Outlet/>);
}
