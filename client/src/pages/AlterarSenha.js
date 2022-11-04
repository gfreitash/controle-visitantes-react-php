import React, {useRef, useState} from "react";
import {Button, Form} from "react-bootstrap";
import {Link, useNavigate} from "react-router-dom";

import useAxiosPrivate from "../hooks/useAxiosPrivate";
import useAuth from "../hooks/useAuth";

import Titulo from "../components/Titulo";
import Alerta from "../components/Alerta";

export default function AlterarSenha() {
    const axios = useAxiosPrivate();
    const navigate = useNavigate();
    const {auth, setAuth} = useAuth();

    const [form, setForm] = useState({senhaAtual:"", novaSenha: "", confirmarNovaSenha:""});
    const [alerta, setAlerta] = useState({tipo: "", mensagem: ""});
    const autoComplete = "new-password";

    const senhaAtualRef = useRef();

    const handleChangeSenhaAtual = (event) => {
        senhaAtualRef.current.classList.remove("is-invalid");
        setForm({
            ...form,
            senhaAtual: event.target.value
        });
    }

    const handleChangeNovaSenha = (event) => {
        if (event.target.value.length < 8) {
            event.target.classList.add("is-invalid");
        } else {
            event.target.classList.remove("is-invalid");
            event.target.classList.add("is-valid");
        }

        setForm({...form, novaSenha: event.target.value})
    }
    const handleChangeConfirmarNovaSenha = (event) => {
        if (event.target.value !== form.novaSenha) {
            event.target.classList.add("is-invalid");
        } else {
            event.target.classList.remove("is-invalid");
            event.target.classList.add("is-valid");
        }

        setForm({...form, confirmarNovaSenha: event.target.value})
    }

    const handleSubmit = async (event) => {
        event.preventDefault();

        if (form.novaSenha !== form.confirmarNovaSenha) {
            setAlerta({tipo: "danger", mensagem: "As senhas não coincidem"});
            return;
        } else if (form.novaSenha.length < 8) {
            setAlerta({tipo: "danger", mensagem: "A senha deve ter no mínimo 8 caracteres"});
            return;
        }

        let formData = new FormData();
        formData.append("senhaAtual", form.senhaAtual);
        formData.append("novaSenha", form.novaSenha);
        formData.append("confirmarNovaSenha", form.confirmarNovaSenha);
        formData.append("id", auth.id);

        try {
            const resposta = await axios.put("/usuario", formData);
            if (resposta.status === 200) {
                setAuth({
                    ...auth,
                    alerta: {
                        tipo: "success",
                        mensagem: "Senha alterada com sucesso"
                    }
                });

                navigate("/inicio");
            }
        } catch (error) {
            if (error.response.status === 400 && error.response.data.error === "Senha atual incorreta") {
                setAlerta({tipo: "danger", mensagem: "A senha atual incorreta"});
                senhaAtualRef.current.classList.add("is-invalid");
            } else {
                console.error(error.response?.data);
            }
        }
    }
    return (
        <>
            <Titulo>Alterar senha</Titulo>
            <hr/>
            <Alerta alerta={alerta} setAlerta={setAlerta}/>
            <section className="d-flex">
                <Form onSubmit={handleSubmit}>
                    <Form.Group controlId="senhaAtual">
                        <Form.Label>
                            Senha atual:
                        </Form.Label>
                        <Form.Control ref={senhaAtualRef} type="password" name="senhaAtual" onChange={handleChangeSenhaAtual}/>
                        <Form.Control.Feedback type="invalid">
                            A senha está incorreta.
                        </Form.Control.Feedback>
                    </Form.Group>

                    <hr/>

                    <Form.Group controlId="novaSenha" className="mt-2">
                        <Form.Label>
                            Nova senha:
                        </Form.Label>
                        <Form.Control type="password" name="novaSenha" onChange={handleChangeNovaSenha}
                                      autoComplete={autoComplete}
                        />
                        <Form.Text>
                            Sua senha deve conter pelo menos 8 caracteres.
                        </Form.Text>
                    </Form.Group>

                    <Form.Group controlId="confirmarNovaSenha" className="mt-1">
                        <Form.Label>
                            Confirmar nova senha
                        </Form.Label>
                        <Form.Control type="password" name="confirmarNovaSenha" onChange={handleChangeConfirmarNovaSenha}
                                      autoComplete={autoComplete}
                        />
                        <Form.Control.Feedback type="invalid">
                            As senhas não coincidem.
                        </Form.Control.Feedback>
                    </Form.Group>

                    <div className="d-flex justify-content-start mt-3">
                        <Link to="/inicio" className="me-2">
                            <Button variant="secondary" type="button">Cancelar</Button>
                        </Link>
                        <Button type={"submit"}>Alterar</Button>
                    </div>
                </Form>
            </section>
        </>
    )
}
