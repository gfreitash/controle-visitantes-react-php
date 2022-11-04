import React from "react";

import useAuth from "./useAuth";
import axios from "../api/axios";

export default function useRefreshToken() {
    const {setAuth} = useAuth();

    return async () => {
        const response = await axios.get("/refresh", {withCredentials: true});

        setAuth({
            nome: response.data.nome,
            id: response.data.id,
            email: response.data.email,
            accessToken: response.data.accessToken
        });

        return response.data.accessToken;
    };
}
