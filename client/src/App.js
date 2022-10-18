import React from "react";

import 'bootstrap/dist/css/bootstrap.min.css';
import "./assets/css/style.css"

import {Routes, Route} from "react-router-dom";

import Header from "./components/Header";
import Footer from "./components/Footer";
import Main from "./components/Main";
import useAuth from "./hooks/useAuth";
import NovoCadastro from "./pages/NovoCadastro";
import ListaVisitantes from "./pages/ListaVisitantes";
import Visitante from "./pages/Visitante";
import NovaVisita from "./pages/NovaVisita";
import Visita from "./pages/Visita";
import ListaVisitas from "./pages/ListaVisitas";

export default function App() {
    const {auth} = useAuth();

    return (
        <div className="wrapper">
            <Header usuario={auth.nome}/>
            <Main>
                <Routes>
                    <Route path="/novo-cadastro" element={<NovoCadastro/>}/>
                    <Route path="/lista-visitantes" element={<ListaVisitantes/>}/>
                    <Route path="/visitante" element={<Visitante/>}/>
                    <Route path="/nova-visita" element={<NovaVisita/>}/>
                    <Route path="/lista-visitas/:id" element={<ListaVisitas/>}/>
                    <Route path="/visita" element={<Visita/>}/>
                </Routes>
            </Main>
            <Footer/>
        </div>
    )
}
