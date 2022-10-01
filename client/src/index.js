import React from 'react';
import ReactDOM from 'react-dom/client';
import {BrowserRouter as Router, Routes, Route} from "react-router-dom";

import App from "./App";
import Login from "./pages/Login";
import {ProvedorAutenticacao} from "./context/ProvedorAutenticacao";
import RequerAutenticacao from "./components/RequerAutenticacao";

const root = ReactDOM.createRoot(document.getElementById('root'));

root.render(
    <React.StrictMode>
        <Router>
            <ProvedorAutenticacao>
                <Routes>
                    <Route path="/login" element={<Login/>}/>
                    <Route element={<RequerAutenticacao />}>
                        <Route path="/*" element={<App/>}/>
                    </Route>
                </Routes>
            </ProvedorAutenticacao>
        </Router>
    </React.StrictMode>
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
