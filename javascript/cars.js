document.addEventListener("DOMContentLoaded", () => {
    const cards = document.querySelectorAll(".car-card");
  
    cards.forEach(card => {
      card.addEventListener("click", () => {
        cards.forEach(c => {
          c.classList.remove("active");
          c.querySelector(".book-now").classList.add("hidden");
          c.querySelector(".specs").style.display = "flex";
        });
  
        card.classList.add("active");
        card.querySelector(".book-now").classList.remove("hidden");
        card.querySelector(".specs").style.display = "none";
      });
    });
  
    const filters = document.querySelectorAll(".filters select");
    filters.forEach(filter => {
      filter.addEventListener("change", () => {
        applyFilters();
      });
    });
  
    function applyFilters() {
      const price = document.getElementById("priceFilter").value;
      const manufacturer = document.getElementById("manufacturerFilter").value;
      const type = document.getElementById("typeFilter").value;
      const rating = document.getElementById("ratingFilter").value;
  
      document.querySelectorAll(".car-card").forEach(card => {
        let show = true;
  
        if (price !== "Price" && card.dataset.price !== price) show = false;
        if (manufacturer !== "Manufacturer" && card.dataset.manufacturer !== manufacturer) show = false;
        if (type !== "Type" && card.dataset.type !== type) show = false;
        if (rating !== "Rating" && card.dataset.rating !== rating) show = false;
  
        card.style.display = show ? "block" : "none";
      });
    }
  });
  