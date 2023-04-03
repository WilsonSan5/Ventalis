//clé public de stripe mode dev
console.log('paiement js')

const stripe = Stripe('pk_test_51Mf1j1FufBPCUONNqyxowfyRialcOGzND5Xpi7uudebU7aqvQBKZqVd9WadEzHI0Rmh4eO8IxVOVz1J6kk1CKDC500XpGy6bvU')


//Pointer sur le nom de domaine

var id_produit = document.getElementById('id_produit').innerHTML

const originUrl = document.location.origin
const intentUrl = originUrl + '/intentPayment'
const successUrl = originUrl +'/'+ id_produit +'/confirmation'

console.log('originUrl :'+originUrl)
console.log(id_produit)


var price = document.getElementById('prix').innerText
var nomUser = document.getElementById('nomUser').innerText
var prenomUser = document.getElementById('prenomUser').innerText

console.log(price, nomUser, prenomUser)

const items = [{ prix: price, prenom: prenomUser, nom: nomUser }];

console.log(items)

let elements;


initialize();
checkStatus();


document.getElementById("payment-form").addEventListener("submit", handleSubmit);

var emailAddress = '';

// Fetches a payment intent and captures the client secret
async function initialize() {
  const { clientSecret } = await fetch(intentUrl, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ items }),
  }).then((r) => r.json());

  elements = stripe.elements({ clientSecret });


  const linkAuthenticationElement = elements.create("linkAuthentication");
  linkAuthenticationElement.mount("#link-authentication-element");

  const paymentElementOptions = {
    layout: "tabs",
  };

  const paymentElement = elements.create("payment", paymentElementOptions);
  paymentElement.mount("#payment-element");
}

async function handleSubmit(e) {
  e.preventDefault();
  setLoading(true);

  const { error } = await stripe.confirmPayment({
    elements,
    confirmParams: {
      // Make sure to change this to your payment completion page
      return_url: successUrl,
      receipt_email: emailAddress,
    },
  });

	  // This point will only be reached if there is an immediate error when
	  // confirming the payment. Otherwise, your customer will be redirected to
	  // your `return_url`. For some payment methods like iDEAL, your customer will
	  // be redirected to an intermediate site first to authorize the payment, then
	  // redirected to the `return_url`.
  if (error.type === "card_error" || error.type === "validation_error") {
    showMessage(error.message);
  } else {
    showMessage("Une erreur inattendue s'est produite");
  }

  setLoading(false);
}

// Fetches the payment intent status after payment submission
async function checkStatus() {
  const clientSecret = new URLSearchParams(window.location.search).get(
    "payment_intent_client_secret"
  );

  if (!clientSecret) {
    return;
  }

  const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret);

  switch (paymentIntent.status) {
    case "succeeded":
      showMessage("Le paiement a réussi !");
      break;
    case "processing":
      showMessage("Votre paiement est en cours de traitement.");
      break;
    case "requires_payment_method":
      showMessage("Votre paiement n'a pas été réussi, s'il vous plaît réessayer");
      break;
    default:
      showMessage("Quelque chose a mal tourné.");
      break;
  }
}

// ------- UI helpers -------

function showMessage(messageText) {
  const messageContainer = document.querySelector("#payment-message");

  messageContainer.classList.remove("hidden");
  messageContainer.textContent = messageText;

  setTimeout(function () {
    messageContainer.classList.add("hidden");
    messageText.textContent = "";
  }, 4000);
}

// Show a spinner on payment submission
function setLoading(isLoading) {
  if (isLoading) {
    // Disable the button and show a spinner
    document.querySelector("#submit").disabled = true;
    document.querySelector("#spinner").classList.remove("hidden");
    document.querySelector("#button-text").classList.add("hidden");
  } else {
    document.querySelector("#submit").disabled = false;
    document.querySelector("#spinner").classList.add("hidden");
    document.querySelector("#button-text").classList.remove("hidden");
  }
}