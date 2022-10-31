import {axiosPrivate} from "../api/axios";
import useAuth from "./useAuth";

import React from "react";

export default function useRefreshToken() {
    const {setAuth} = useAuth();
    const axios = axiosPrivate;

    return async () => {
        const response = await axios.get("/refresh");

        setAuth({
            nome: response.data.nome,
            id: response.data.id,
            email: response.data.email,
            accessToken: response.data.accessToken
        });

        return response.data.accessToken;
    };
}
