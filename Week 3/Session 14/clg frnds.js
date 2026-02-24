// ===============================
// Create Two Friend Lists
// ===============================
const collegeFriends = ["Arun", "Divya", "Karthik"];
const workFriends = ["Priya", "Rahul", "Sneha"];

// ===============================
// Merge Lists using Spread
// Add "Me" at the beginning
// ===============================
const partyList = ["Me", ...collegeFriends, ...workFriends];

console.log("🎉 Party Welcome List:");
console.log(partyList);

// ===============================
// Function using Normal Parameter + REST Operator
// ===============================
const welcomeGuests = (host, ...guests) => {
    console.log(`\n🎊 Host: ${host}`);
    console.log("👋 Guests:");

    guests.forEach((guest, index) => {
        console.log(`${index + 1}. ${guest}`);
    });
};

// ===============================
// Call Function using Spread
// ===============================
welcomeGuests(partyList[0], ...partyList.slice(1));