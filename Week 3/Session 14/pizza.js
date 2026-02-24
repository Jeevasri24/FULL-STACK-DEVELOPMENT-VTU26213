// ===============================
// Pizza Ordering System using Promise
// ===============================

const orderPizza = (pizzaType) => {
    return new Promise((resolve, reject) => {

        console.log(`📝 Order received for ${pizzaType} pizza...`);

        // Simulate preparation time (3 seconds)
        setTimeout(() => {

            // Random availability (true/false)
            const available = Math.random() > 0.3;

            if (available) {
                resolve(`🍕 Your ${pizzaType} pizza is ready! Enjoy your meal!`);
            } else {
                reject(`❌ Sorry! ${pizzaType} pizza is out of stock.`);
            }

        }, 3000);
    });
};

// ===============================
// Using .then() and .catch()
// ===============================
orderPizza("Margherita")
    .then((message) => {
        console.log("✅ Using .then():");
        console.log(message);
    })
    .catch((error) => {
        console.log("⚠ Using .catch():");
        console.log(error);
    });

// ===============================
// Using async/await
// ===============================
const placeOrder = async () => {
    try {
        const message = await orderPizza("Pepperoni");
        console.log("✅ Using async/await:");
        console.log(message);
    } catch (error) {
        console.log("⚠ Using async/await:");
        console.log(error);
    }
};

// Call async function
placeOrder();