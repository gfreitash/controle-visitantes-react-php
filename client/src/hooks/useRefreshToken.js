import axios from "../api/axios";
import useAuth from "./useAuth";

import React from "react";

export default function useRefreshToken() {
    const {setAuth} = useAuth();

    return async () => {
        const response = await axios.get("/refresh", {
            withCredentials: true
        });

        setAuth(prevAuth => {
            return {...prevAuth, accessToken: response.data.accessToken}
        });

        return response.data.accessToken;
    };
}
