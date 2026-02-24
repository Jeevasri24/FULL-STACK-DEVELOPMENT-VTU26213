// Complex Object
const order = {
    orderId: 101,
    customer: {
        name: "Jeevasri",
        email: "jeevasri@email.com"
    },
    items: ["Laptop", "Mouse", "Keyboard"],
    payment: {
        method: "UPI",
        status: "Paid"
    }
};

// =============================
// Object Destructuring
// =============================

// Extract nested properties
const {
    orderId,
    customer: { name, email },
    payment: { method }
} = order;

// =============================
// Array Destructuring
// =============================

// Extract elements from array
const [firstItem, secondItem] = order.items;

// =============================
// Output
// =============================

console.log(`Order ID: ${orderId}`);
console.log(`Customer Name: ${name}`);
console.log(`Email: ${email}`);
console.log(`Payment Method: ${method}`);
console.log(`First Item: ${firstItem}`);
console.log(`Second Item: ${secondItem}`);