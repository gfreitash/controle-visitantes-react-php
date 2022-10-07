import React, {useRef, useState, useEffect} from "react";
import {useNavigate, useLocation} from "react-router-dom";
import useAuth from "../hooks/useAuth";

import {InputGroup, Form} from "react-bootstrap";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faUser, faLock} from "@fortawesome/free-solid-svg-icons";

import "../assets/css/login.css";
import logo from "../assets/imgs/logo.png";
import axios from "../api/axios";

export default function Login() {
    const LOGIN_URL = "/login";
    const {auth, setAuth} = useAuth();

    const navigate = useNavigate();
    const location = useLocation();
    const from = location.state?.from?.pathname + location.state?.from?.search  || "/";

    const emailRef = useRef();
    const alertaRef = useRef();

    const [email, setEmail] = useState("");
    const [senha, setSenha] = useState("");
    const [alerta, setAlerta] = useState({});

    useEffect(() => {
        if (auth?.accessToken) {
            navigate(from);
            return;
        }

        if (auth?.alerta) {
            alertaRef.current.className = `alert alert-${auth.alerta.tipo}`;
            alertaRef.current.innerText = auth.alerta.mensagem;
        }

        emailRef.current.focus();
    }, []);

    useEffect(() => {
        setAlerta({});
    }, [email, senha]);


    const handleSubmit = async (evt) => {
        evt.preventDefault();

        try {
            const formData = new FormData(evt.target);
            const response = await axios.post(LOGIN_URL, formData, {
                withCredentials: true
            });

            const accessToken = response?.data?.accessToken ? "Bearer " + response?.data?.accessToken : null;
            const nome = response?.data?.nome;
            const id = response?.data?.id;

            setAuth({nome, id, email, accessToken});
            setEmail("");
            setSenha("");

            navigate(from, {replace: true});
        } catch (err) {
            if(err.response?.status === 0) {
                setAlerta({mensagem: "Sem conexão com o servidor.", tipo: "danger"});
            } else if (err.response?.status === 400) {
                setAlerta({mensagem: "Há um campo inválido!", tipo: "danger"});
            } else if (err.response?.status === 401) {
                setAlerta({mensagem: "Email ou senha estão incorretos!", tipo: "danger"});
            } else {
                setAlerta({mensagem: "Não foi possível fazer o login.", tipo: "danger"});
            }
            alertaRef.current.focus();
            setAuth({});
        }
    }
    return (
        <div className="pagina">
            <div className="card">
                <img src={logo} alt="logo" width="150px" height="115px"/>
                <br/>
                <b>Controle de Visitantes</b>
                <br/>
                <div id="alerta" ref={alertaRef} className={alerta.mensagem ? `alert alert-${alerta.tipo}` : ""}>{alerta.mensagem}</div>
                <p className="text-muted">Faça login para acessar o sistema</p>
                <form onSubmit={handleSubmit} className="login-form" id="login-form">
                    <InputGroup className="mb-3">
                        <InputGroup.Text>
                            <FontAwesomeIcon icon={faUser}/>
                        </InputGroup.Text>
                        <Form.Control ref={emailRef} onChange={(e) => setEmail(e.target.value)} value={email}
                                      type="text" name="email" placeholder="E-mail" required/>
                    </InputGroup>
                    <InputGroup className="mb-4">
                        <InputGroup.Text>
                            <FontAwesomeIcon icon={faLock}/>
                        </InputGroup.Text>
                        <Form.Control onChange={(e) => setSenha(e.target.value)} value={senha}
                                      type="password" name="senha" placeholder="Senha" required/>
                    </InputGroup>
                    <button type="submit" className="btn btn-primary">Entrar</button>
                </form>
            </div>
        </div>
    )
}
