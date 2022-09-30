import React, {createContext, useState} from "react";

const AuthContext = createContext({});

export const ProvedorAutenticacao = ({children}) => {
    const [auth, setAuth] = useState({});

    return (
        <AuthContext.Provider value={{auth, setAuth}}>
            {children}
        </AuthContext.Provider>
    )
}

export default AuthContext;