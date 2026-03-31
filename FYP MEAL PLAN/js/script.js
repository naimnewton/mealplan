// Validasi input sebelum submit
document.addEventListener("DOMContentLoaded", function() {
  const form = document.querySelector("form");

  form.addEventListener("submit", function(e) {
    const height = document.querySelector('input[name="height"]').value;
    const weight = document.querySelector('input[name="weight"]').value;
    const name = document.querySelector('input[name="name"]').value;

    // Cegah form kosong
    if (name.trim() === "" || height === "" || weight === "") {
      alert("⚠️ Sila isi semua maklumat sebelum meneruskan!");
      e.preventDefault(); // Hentikan submit
      return false;
    }

    // Semak nilai logik
    if (height < 100 || height > 250) {
      alert("⚠️ Nilai tinggi tidak realistik. Masukkan antara 100–250 cm.");
      e.preventDefault();
      return false;
    }

    if (weight < 30 || weight > 200) {
      alert("⚠️ Nilai berat tidak realistik. Masukkan antara 30–200 kg.");
      e.preventDefault();
      return false;
    }

    // Papar mesej loading
    alert("⏳ Sistem sedang mengira pelan anda...");
  });
});

// Highlight hasil BMI bila siap dikira
window.addEventListener("load", function() {
  const bmiElement = document.querySelector(".result p strong");
  if (bmiElement) {
    bmiElement.style.color = "#007bff";
  }
});
