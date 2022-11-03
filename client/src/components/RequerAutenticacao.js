import {Outlet} from "react-router-dom";
import useAuth from "../hooks/useAuth";
import useInvalidSessionHandler from "../hooks/useInvalidSessionHandler";
import {useState} from "react";
import useRefreshToken from "../hooks/useRefreshToken";
import useEffectOnce from "../hooks/useEffectOnce";


export default function RequerAutenticacao() {
    const {auth} = useAuth();
    const refresh = useRefreshToken();
    const handleInvalidSession = useInvalidSessionHandler();

    const [carregando, setCarregando] = useState(true);

    useEffectOnce(() => {
        console.log("RequerAutenticacao: useEffectOnce");
        const verificarLogin = async () => {
            try {
                await refresh();
            } catch (error) {
                if (error.response?.status === 401) {
                    handleInvalidSession();
                }
            } finally {
                setCarregando(false);
            }
        }

        !auth?.accessToken ? verificarLogin(): setCarregando(false);
    },[]);

    return carregando ? (<div>Carregando...</div>) : <Outlet/>;
}
