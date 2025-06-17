document.querySelector('.hamburger').addEventListener('click', () => {
  document.querySelector('.nav-links').classList.toggle('show');
});

document.getElementById('bookingForm').addEventListener('submit', function(e) {
  e.preventDefault();
  document.getElementById('confirmation').innerText = "✅ Your ride has been booked!";
  this.reset();
});
