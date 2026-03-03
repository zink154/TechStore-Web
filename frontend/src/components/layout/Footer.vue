<template>
  <footer class="bg-gray-900 text-gray-400 mt-auto">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div>
          <h3 class="text-white text-lg font-bold mb-3">TechStore</h3>
          <p class="text-sm">Your one-stop shop for the latest tech products.</p>
        </div>
        <div>
          <h4 class="text-white text-sm font-semibold mb-3">Quick Links</h4>
          <ul class="space-y-2 text-sm">
            <li><RouterLink to="/products" class="hover:text-white">Products</RouterLink></li>
            <li><RouterLink to="/cart" class="hover:text-white">Cart</RouterLink></li>
            <li><RouterLink to="/orders" class="hover:text-white">My Orders</RouterLink></li>
          </ul>
        </div>
        <div>
          <h4 class="text-white text-sm font-semibold mb-3">Contact</h4>
          <p class="text-sm">demo@techstore.test</p>
          <p class="text-sm mt-1">This is a demo project.</p>
        </div>
      </div>
      <div class="border-t border-gray-800 mt-8 pt-6 text-center text-sm">
        &copy; {{ new Date().getFullYear() }} TechStore. Demo project for portfolio.
      </div>
    </div>
  </footer>
</template>
