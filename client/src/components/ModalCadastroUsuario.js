import React, {useRef, useState} from "react";
import {Form, Modal} from "react-bootstrap";
import useAxiosPrivate from "../hooks/useAxiosPrivate";
import useAuth from "../hooks/useAuth";

export default function ModalCadastroUsuario({exibir, onFechar}) {
    const axios = useAxiosPrivate();
    const {auth} = useAuth();

    const [form, setForm] = useState({
        nome: "",
        email: "",
        administrador: false
    });
    const [confirmacao, setConfirmacao] = useState(false);

    const formRef = useRef();

    const limparForm = () => {
        setForm({
            nome: "",
            email: "",
            administrador: false
        })
    }

    const handleChange = (e) => {
        if (!e.target.value) {
            e.target.classList.add("is-invalid");
        } else {
            e.target.classList.remove("is-invalid");
        }

        setForm({...form, [e.target.name]: e.target.value});
    }

    const handleFechar = (sucesso) => {
        setConfirmacao(false);

        if (!confirmacao || sucesso) {
            limparForm();
            onFechar();
        }
    }

    const handleCadastrar = () => {
        if (!form.nome) {
            formRef.current.nome.classList.add("is-invalid");
        } else if (!form.email) {
            formRef.current.email.classList.add("is-invalid");
        }
        if (!formRef.current.checkValidity()) {
            formRef.current.reportValidity();
            return;
        }

        if (form.nome && form.email) {
            formRef.current.nome.classList.remove("is-invalid");
            formRef.current.email.classList.remove("is-invalid");

            if (!confirmacao) {
                setConfirmacao(true);
                return;
            }

            formRef.current?.requestSubmit();
        }
    }

    const handleSubmit = async (e) => {
        e.preventDefault();

        try {
            let formData = new FormData();
            formData.append("idUsuario", auth.id);
            formData.append("nome", form.nome);
            formData.append("email", form.email);
            formData.append("administrador", form.administrador);

            const resposta = await axios.post("/usuario", formData);

            if (resposta.status === 200 || resposta.status === 201) {
                alert("Usuário cadastrado com sucesso!");
                handleFechar(true);
            }
        } catch (e) {
            alert("Não foi possível cadastrar o usuário");
        } finally {
            setConfirmacao(false);
        }
    }

    return (
        <Modal
            show={exibir}
            onHide={onFechar}
            backdrop="static"
            keyboard={false}
        >
            <Modal.Header closeButton>
                <Modal.Title>Cadastrar novo usuário</Modal.Title>
            </Modal.Header>

            <Modal.Body>
                <Form onSubmit={handleSubmit} ref={formRef} style={!confirmacao ? {display: "block"} : {display: "none"}}>
                    <Form.Group controlId="nome">
                        <Form.Label>Nome</Form.Label>
                        <Form.Control type="text" placeholder="Digite o nome do usuário" value={form.nome} name="nome"
                                      onChange={handleChange} required/>
                        <Form.Control.Feedback type="invalid">
                            Por favor, digite o nome do usuário.
                        </Form.Control.Feedback>
                    </Form.Group>

                    <Form.Group controlId="email" className="mt-2">
                        <Form.Label>Email</Form.Label>
                        <Form.Control type="email" placeholder="Digite o email do usuário" value={form.email} name="email"
                                      onChange={handleChange} required/>
                        <Form.Control.Feedback type="invalid">
                            Por favor, digite o email do usuário.
                        </Form.Control.Feedback>
                    </Form.Group>

                    <Form.Check
                        className="mt-3"
                        id="isAdministrador"
                        label="Administrador"
                        checked={form.administrador}
                        onChange={(e) => setForm({...form, administrador: e.target.checked})}
                    />
                </Form>

                {confirmacao && (
                    <div>
                        <p>Deseja realmente cadastrar o {form.administrador ? "administrador" : "usuário"} <b>"{form.nome}"</b> com o email <b>"{form.email}"</b> ?</p>
                    </div>
                )}
            </Modal.Body>

            <Modal.Footer>
                <button className="btn btn-secondary" onClick={handleFechar}>Cancelar</button>
                <button className="btn btn-primary" onClick={handleCadastrar}>Cadastrar</button>
            </Modal.Footer>
        </Modal>
    )
}
