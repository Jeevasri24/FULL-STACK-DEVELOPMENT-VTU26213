const generateReceipt = (price, tip) => {
    const total = price + tip;
    console.log(`🧾 Receipt:
Price: ₹${price}
Tip: ₹${tip}
Total Amount: ₹${total}`);
};