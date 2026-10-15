window.addEventListener('load', () => {
  const productCards = document.querySelectorAll('.product-card');
  productCards.forEach((card, index) => {
    setTimeout(() => {
      card.classList.add('appear');
    }, index * 100);
  });
});