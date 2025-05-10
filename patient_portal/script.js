document.addEventListener("DOMContentLoaded", () => {
    // Render the Appointments Overview chart if the canvas exists
    const ctx = document.getElementById("myChart");
    if (ctx) {
      new Chart(ctx, {
        type: "line",
        data: {
          labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"],
          datasets: [
            {
              label: "Appointments",
              data: [10, 15, 8, 12, 20, 18, 25],
              borderColor: "#4a69bd",
              fill: false,
              tension: 0.1,
            },
          ],
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      });
    }
  
    // Render the Health Statistics chart if the canvas exists
    const ctxHealth = document.getElementById("healthChart");
    if (ctxHealth) {
      new Chart(ctxHealth, {
        type: "bar",
        data: {
          labels: ["BP", "Sugar", "Weight", "Cholesterol"],
          datasets: [
            {
              label: "Your Health Data",
              data: [120, 90, 70, 180],
              backgroundColor: "#40739e",
            },
          ],
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true,
            },
          },
        },
      });
    }
  
    // Add event handler for the Symptom Checker button
    const chatbotButton = document.querySelector(".chatbot button");
    if (chatbotButton) {
      chatbotButton.addEventListener("click", () => {
        const input = document.querySelector(".chatbot input[type='text']");
        if (input && input.value.trim() !== "") {
          // You can replace this alert with an actual symptom check implementation.
          alert("Symptom checker is under development. You entered: " + input.value);
        } else {
          alert("Please enter your symptoms first.");
        }
      });
    }
  });
  