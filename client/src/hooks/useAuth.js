import {useContext} from "react";
import AuthContext from "../context/ProvedorAutenticacao";

export default function useAuth() {
    return useContext(AuthContext);
}