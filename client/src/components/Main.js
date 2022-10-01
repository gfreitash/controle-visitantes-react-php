import React from "react";

export default function Main(props) {
    return (
        <main>
            <div className="main-container">
                {props.children}
            </div>
        </main>
    )
}