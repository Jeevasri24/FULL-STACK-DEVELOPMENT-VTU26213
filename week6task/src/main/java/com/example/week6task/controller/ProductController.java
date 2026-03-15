package com.example.week6task.controller;

import java.util.ArrayList;
import java.util.List;

import org.springframework.web.bind.annotation.*;

import com.example.week6task.Product;
import com.example.week6task.User;

@RestController
public class ProductController {

    List<Product> products = new ArrayList<>();

    // Add Product
    @PostMapping("/addProduct")
    public Product addProduct(@RequestBody Product product) {
        products.add(product);
        return product;
    }

    // Get All Products
    @GetMapping("/products")
    public List<Product> getProducts() {
        return products;
    }

    // Get Product by ID
    @GetMapping("/product/{id}")
    public Product getProduct(@PathVariable int id) {
        for (Product p : products) {
            if (p.getId() == id) {
                return p;
            }
        }
        return null;
    }

    // Update Product
    @PutMapping("/update/{id}")
    public Product updateProduct(@PathVariable int id, @RequestBody Product product) {
        for (Product p : products) {
            if (p.getId() == id) {
                p.setName(product.getName());
                p.setPrice(product.getPrice());
                return p;
            }
        }
        return null;
    }

    // Delete Product
    @DeleteMapping("/delete/{id}")
    public String deleteProduct(@PathVariable int id) {
        products.removeIf(p -> p.getId() == id);
        return "Product Deleted";
    }

    // RequestParam Example
    @GetMapping("/greet")
    public String greet(@RequestParam String name) {
        return "Hello " + name;
    }

    // PathVariable Example
    @GetMapping("/square/{num}")
    public int square(@PathVariable int num) {
        return num * num;
    }

    // Register User API
    @PostMapping("/register")
    public String register(@RequestBody User user) {
        return "User Registered Successfully";
    }

}