import React from "react";
import {Form} from "react-bootstrap";

export default function DadosVisita(props) {

    const foi_liberado = {
        flexGrow: "1",
        display: "flex",
        alignItems: "center"
    }

    return (
        <div className="form-linha">
            <div className="form-linha width--80">
                <Form.Group controlId="salaVisita" className="campo width--33">
                    <Form.Label>Onde vai:</Form.Label>
                    <Form.Control type="text" name="salaVisita" disabled={props.disabled} required/>
                </Form.Group>

                <Form.Group controlId="motivoVisita" className="campo width--65">
                    <Form.Label>Motivo da visita:</Form.Label>
                    <Form.Control type="text" name="motivoVisita" disabled={props.disabled}/>
                </Form.Group>
            </div>

            <div className="campo width--18">
                <label className="form-label">Foi liberado?</label>
                <div key="inline-radio" style={foi_liberado}>
                    <Form.Check inline required label="Sim" name="foiLiberado"
                                type="radio" id="radio-sim" value="1"/>
                    <Form.Check inline required label="Não" name="foiLiberado"
                                type="radio" id="radio-nao" value="0"/>
                </div>
            </div>
        </div>
    )
}
