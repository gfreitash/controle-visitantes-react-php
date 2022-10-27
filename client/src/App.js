import React from "react";

import 'bootstrap/dist/css/bootstrap.min.css';
import "./assets/css/style.css"

import {Routes, Route} from "react-router-dom";
import useAuth from "./hooks/useAuth";

import Header from "./components/Header";
import Footer from "./components/Footer";
import Main from "./components/Main";

import NovoCadastro from "./pages/NovoCadastro";
import ListaVisitantes from "./pages/ListaVisitantes";
import Visitante from "./pages/Visitante";
import NovaVisita from "./pages/NovaVisita";
import Visita from "./pages/Visita";
import ListaVisitas from "./pages/ListaVisitas";
import ModalRelatorioVisitas from "./pages/ModalRelatorioVisitas";

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
                    <Route path="/relatorio-visitas" element={<ModalRelatorioVisitas/>}/>
                    <Route path="*" element={<h1>404 - Página não encontrada</h1>}/>
                </Routes>
            </Main>
            <Footer/>
        </div>
    )
}
