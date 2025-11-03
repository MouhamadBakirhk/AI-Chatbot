// --- JWT Helpers ---
function parseJwt(token) {
    try { return JSON.parse(atob(token.split('.')[1])); } 
    catch(e) { return null; }
}
function isTokenValid(token) {
    if(!token) return false;
    const payload = parseJwt(token);
    if(!payload || !payload.exp) return false;
    return payload.exp > Math.floor(Date.now() / 1000);
}

// --- Chatbot ---
const chatbot = document.getElementById("chatbot");
const toggleBtn = document.getElementById("chatbot-toggle");
const closeBtn = document.getElementById("close-chat");
const messagesBox = document.getElementById("chatbot-messages");
const inputField = document.getElementById("userMessage");
const sendBtn = document.getElementById("sendMessage");

toggleBtn.addEventListener("click", () => chatbot.style.display = "flex");
closeBtn.addEventListener("click", () => chatbot.style.display = "none");
sendBtn.addEventListener("click", sendMessage);
inputField.addEventListener("keypress", e => { if(e.key==="Enter") sendMessage(); });

function appendMessage(sender, text){
    messagesBox.innerHTML += `<p><strong>${sender}:</strong> ${text}</p>`;
    messagesBox.scrollTop = messagesBox.scrollHeight;
}

function sendMessage(){
    const message = inputField.value.trim();
    if(!message) return;
    appendMessage("You", message);
    inputField.value = "";

    fetch("http://127.0.0.1:5000/chatbot", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ message })
    })
    .then(res => res.json())
    .then(data => {
        let reply = "";
        if(typeof data.reply === "string") {
            reply = data.reply;
        } else if(data.choices && data.choices[0]){
            reply = (data.choices[0].message?.content) || data.choices[0].text || JSON.stringify(data.choices[0]);
        } else if(data.error){
            reply = data.error;
        } else {
            reply = "No reply";
        }
        appendMessage("Bot", reply);
    })
    .catch(err => appendMessage("Bot", "Error connecting to server."));
}

// --- Sidebar ---
document.getElementById("sidebar-container").innerHTML = `
<div class="sidebar">
  <h5 class="px-3">Categories</h5>
  <ul class="nav flex-column px-3">
    <li class="nav-item"><a class="nav-link category-link" href="#" data-category="All">All Products</a></li>
    <li class="nav-item"><a class="nav-link category-link" href="#" data-category="Electronics">Electronics</a></li>
    <li class="nav-item"><a class="nav-link category-link" href="#" data-category="Clothes">Clothes</a></li>
    <li class="nav-item"><a class="nav-link category-link" href="#" data-category="Books">Books</a></li>
  </ul>
</div>`;

// --- Products ---
function renderProducts(products){
  const container = document.getElementById("products");
  container.innerHTML = "";
  products.forEach(p => {
    container.innerHTML += `
      <div class="col-md-4 mb-4">
        <div class="card product-card">
          <img src="http://127.0.0.1:8000${p.image_url}" class="card-img-top" alt="${p.name}">
          <div class="card-body">
            <h5 class="card-title">${p.name}</h5>
            <p class="card-text">${p.description}</p>
            <p class="card-text fw-bold">$${p.price}</p>
            <p class="text-warning">⭐ ${p.average_rating ?? 0}</p>
            <button class="btn btn-outline-warning me-2" onclick="openRatingModal(${p.id})">Rate Product</button>
            <button class="btn btn-primary" onclick="addToCart(${p.id}, ${p.quantity})">Add to Cart</button>
          </div>
        </div>
      </div>`;
  });
}


async function loadProducts(category="All"){
    try {
        const token = localStorage.getItem('token');
        let res = await fetch("http://127.0.0.1:8000/api/products", {
            headers: token?{"Authorization":"Bearer "+token}:{}
        });
        if(!res.ok) throw new Error("Failed to fetch products");
        let products = await res.json();
        let filtered = (category==="All")?products:products.filter(p=>p.category===category);
        renderProducts(filtered);
    } catch(err){ console.error(err); document.getElementById("products").innerHTML=`<p class="text-danger">Failed to load products.</p>`; }
}

document.querySelectorAll(".category-link").forEach(link=>{
    link.addEventListener("click", e=>{
        e.preventDefault();
        loadProducts(e.target.getAttribute("data-category"));
    });
});

// --- Add to Cart ---
async function addToCart(id, availableQty){
    const token = localStorage.getItem('token');
    if(!token || !isTokenValid(token)){
        alert("Please login first or session expired.");
        window.location.href = "login.html";
        return;
    }

    if(availableQty <= 0){
        alert("Sorry, this product is out of stock!");
        return;
    }

    try {
        const res = await fetch("http://127.0.0.1:8000/api/cart",{
            method:"POST",
            headers:{
                "Content-Type":"application/json",
                "Authorization":"Bearer "+token
            },
            body:JSON.stringify({product_id:id, quantity:1})
        });
        const data = await res.json();
        if(res.ok && data.success){ 
            alert("Product added to cart!"); 
            window.location.href="cart.html"; 
        } else {
            alert("Failed: "+(data.message||"Unknown error"));
        }
    } catch(err){ 
        console.error(err); 
        alert("Error adding to cart!"); 
    }
}


// --- Initial load ---
loadProducts();
window.addEventListener('load', ()=>{
    const role = localStorage.getItem("role");
    if(role!=="admin"){
        const addProductLink = document.querySelector('a[href="add-product.html"]');
        if(addProductLink) addProductLink.style.display="none";
    }
});

const token = localStorage.getItem('token');
if(token && isTokenValid(token)){
    document.querySelector('li.nav-item a[href="login.html"]').parentElement.classList.add("d-none");
    document.getElementById("logoutLink").classList.remove("d-none");
} else {
    document.querySelector('li.nav-item a[href="login.html"]').parentElement.classList.remove("d-none");
    document.getElementById("logoutLink").classList.add("d-none");
}

function logout(){
    localStorage.removeItem("token");
    localStorage.removeItem("role");
    window.location.href = "index.html";
}

// --- Rating System ---
let selectedRating = 0;
let currentProductId = null;

// رسم النجوم وتحديد التقييم
const ratingStars = document.getElementById("rating-stars");
Array.from(ratingStars.children).forEach((star,i)=>{
    star.addEventListener("click", ()=>{
        selectedRating = i+1;
        Array.from(ratingStars.children).forEach((s,j)=>{
            s.style.color = j<selectedRating ? "gold" : "#ccc";
        });
    });
});

function openRatingModal(productId){
    const token = localStorage.getItem("token");
    if(!token || !isTokenValid(token)){
        alert("Please login first!");
        window.location.href="login.html";
        return;
    }
    currentProductId = productId;
    selectedRating = 0;
    Array.from(ratingStars.children).forEach(s=>s.style.color="#ccc");
    document.getElementById("rating-comment").value='';
    const modal = new bootstrap.Modal(document.getElementById("ratingModal"));
    modal.show();
}

document.getElementById("submit-rating").addEventListener("click", async ()=>{
    if(selectedRating===0) return alert("Please select a rating.");
    const token = localStorage.getItem("token");
    try{
        const res = await fetch("http://127.0.0.1:8000/api/ratings",{
            method:"POST",
            headers:{
                "Content-Type":"application/json",
                "Authorization":"Bearer "+token
            },
            body:JSON.stringify({
                product_id: currentProductId,
                rating: selectedRating,
                comment: document.getElementById("rating-comment").value
            })
        });
        const data = await res.json();
        if(res.ok){
            alert("Rating submitted successfully!");
            bootstrap.Modal.getInstance(document.getElementById("ratingModal")).hide();
            loadProducts();
        } else alert(data.error || "Failed to submit rating.");
    } catch(err){
        console.error(err);
        alert("Server error!");
    }
});