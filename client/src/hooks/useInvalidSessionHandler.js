import {useLocation, useNavigate} from "react-router-dom";
import useAuth from "../hooks/useAuth";

export default function useInvalidSessionHandler() {
    const {setAuth} = useAuth();
    const navigate = useNavigate();
    const location = useLocation();

    return (alerta={mensagem: "Sua sessão expirou", tipo:"warning"}, voltar=true) => {
        if(alerta) {
            setAuth( prev => {
                return {...prev, alerta: alerta};
            })
        }
        setAuth( prev => {
            return {...prev, accessToken: null, axiosError: null};
        });

        navigate("/login", voltar ? {state: {from: location}, replace: true} : {});
    };
}
