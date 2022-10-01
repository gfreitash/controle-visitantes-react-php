import React from "react";

import 'bootstrap/dist/css/bootstrap.min.css';
import "./assets/css/style.css"
import "./assets/css/form-cadatro.css"

import {Routes, Route} from "react-router-dom";

import Header from "./components/Header";
import Footer from "./components/Footer";
import Main from "./components/Main";

export default function App() {
    return (
        <div className="wrapper">
            <Header usuario="Admin"/>
            <Main>
                <Routes>
                    <Route path="/" />
                </Routes>
            </Main>
            <Footer/>
        </div>
    )
}
