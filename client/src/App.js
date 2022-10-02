import React from "react";

import 'bootstrap/dist/css/bootstrap.min.css';
import "./assets/css/style.css"
import "./assets/css/form-cadatro.css"

import {Routes, Route} from "react-router-dom";

import Header from "./components/Header";
import Footer from "./components/Footer";
import Main from "./components/Main";
import useAuth from "./hooks/useAuth";
import NovoCadastro from "./pages/NovoCadastro";

export default function App() {
    const {auth} = useAuth();

    return (
        <div className="wrapper">
            <Header usuario={auth?.nome}/>
            <Main>
                <Routes>
                    <Route path="/novo-cadastro" element={<NovoCadastro/>}/>
                </Routes>
            </Main>
            <Footer/>
        </div>
    )
}
