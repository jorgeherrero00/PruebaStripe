<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscripción</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .product-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 10px;
            width: 300px;
            text-align: center;
            border-radius: 5px;
            box-shadow: 2px 2px 10px rgba(0,0,0,0.1);
        }
        .product-card img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .product-card button {
            margin-top: 10px;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        #products {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }
        .payment-methods {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }
        .payment-method {
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .google-pay { background-color: black; color: white; }
        .apple-pay { background-color: #333; color: white; }
        .sepa { background-color: #0066CC; color: white; }
        .ideal { background-color: #FF4081; color: white; }
    </style>
</head>
<body>
    <h2>Selecciona un Plan</h2>

    <div id="products"></div>

    <form id="payment-form">
        <div id="card-element"></div>
        <input type="hidden" id="payment-method" name="payment_method">
        <input type="hidden" id="selected-price" name="selected_price">
    </form>

    <h2>Selecciona un Método de Pago</h2>
    <div id="payment-methods" class="payment-methods">
        <button class="payment-method google-pay" id="google-pay-button">Google Pay</button>
        <button class="payment-method apple-pay" id="apple-pay-button">Apple Pay</button>
        <button class="payment-method sepa" id="sepa-button">SEPA Débito</button>
        <button class="payment-method ideal" id="ideal-button">iDEAL</button>
    </div>

    <button id="subscribe-button">Suscribirse</button>

    <script>
        document.addEventListener("DOMContentLoaded", async () => {
            const stripe = Stripe("{{ env('STRIPE_KEY') }}");
            const elements = stripe.elements();
            const cardElement = elements.create("card");
            cardElement.mount("#card-element");

            // Obtener productos desde Stripe
            fetch("/stripe/products")
                .then(response => response.json())
                .then(products => {
                    let productContainer = document.getElementById("products");
                    products.forEach(product => {
                        let productCard = document.createElement("div");
                        productCard.classList.add("product-card");

                        let productImage = document.createElement("img");
                        productImage.src = product.image || "https://via.placeholder.com/150"; // Imagen por defecto
                        productImage.alt = product.name;

                        let productName = document.createElement("h3");
                        productName.textContent = product.name;

                        let productDescription = document.createElement("div");
                        productDescription.innerHTML = product.description; 

                        let productPrice = document.createElement("p");
                        productPrice.innerHTML = `<strong>${product.amount} ${product.currency}</strong>`;

                        let selectButton = document.createElement("button");
                        selectButton.textContent = "Seleccionar este plan";
                        selectButton.dataset.priceId = product.price_id;
                        selectButton.onclick = () => {
                            document.getElementById("selected-price").value = product.price_id;
                            alert(`Plan seleccionado: ${product.name}`);
                        };

                        productCard.appendChild(productImage);
                        productCard.appendChild(productName);
                        productCard.appendChild(productDescription);
                        productCard.appendChild(productPrice);
                        productCard.appendChild(selectButton);

                        productContainer.appendChild(productCard);
                    });
                });

            async function handlePayment(methodType) {
                const selectedPrice = document.getElementById("selected-price").value;
                if (!selectedPrice) {
                    alert("Selecciona un plan antes de continuar.");
                    return;
                }

                let paymentData = { type: methodType };

                if (methodType === "card") {
                    const { paymentMethod, error } = await stripe.createPaymentMethod({
                        type: "card",
                        card: cardElement
                    });

                    if (error) {
                        alert(error.message);
                        return;
                    }

                    paymentData.payment_method = paymentMethod.id;
                }

                fetch("/subscribe", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({
                        payment_method: paymentData.payment_method || "",
                        price_id: selectedPrice,
                        method_type: methodType
                    })
                })
                .then(response => response.json())
                .then(data => alert(data.message || data.error));
            }

            document.getElementById("google-pay-button").addEventListener("click", () => handlePayment("card"));
            document.getElementById("apple-pay-button").addEventListener("click", () => handlePayment("card"));
            document.getElementById("sepa-button").addEventListener("click", () => handlePayment("sepa_debit"));
            document.getElementById("ideal-button").addEventListener("click", () => handlePayment("ideal"));

            document.getElementById("subscribe-button").addEventListener("click", () => handlePayment("card"));
        });
    </script>
</body>
</html>
