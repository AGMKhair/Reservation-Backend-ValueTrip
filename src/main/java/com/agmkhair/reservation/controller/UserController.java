package com.agmkhair.reservation.controller;

import com.agmkhair.reservation.entry.Users;
import com.agmkhair.reservation.repository.UserRepository;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;


import org.springframework.http.ResponseEntity;
import org.springframework.web.bind.annotation.*;

import java.util.List;
import java.util.Optional;

@RestController
@RequestMapping("/api/users")
@CrossOrigin("*")
public class UserController {

    @Autowired
    private UserRepository userRepository;

    // Create User
    @PostMapping
    public ResponseEntity<Users> createUser(@RequestBody Users user) {
        Users savedUser = userRepository.save(user);
        return ResponseEntity.ok(savedUser);
    }

    // Get All Users
    @GetMapping
    public ResponseEntity<List<Users>> getAllUsers() {
        List<Users> users = userRepository.findAll();
        return ResponseEntity.ok(users);
    }

    // Get User By ID
    @GetMapping("/{id}")
    public ResponseEntity<?> getUserById(@PathVariable Long id) {

        Optional<Users> user = userRepository.findById(id);

        if (user.isPresent()) {
            return ResponseEntity.ok(user.get());
        }

        return ResponseEntity
                .badRequest()
                .body("User not found with id: " + id);
    }

    // Update User
    @PutMapping("/{id}")
    public ResponseEntity<?> updateUser(
            @PathVariable Long id,
            @RequestBody Users updatedUser
    ) {

        Optional<Users> optionalUser = userRepository.findById(id);

        if (optionalUser.isEmpty()) {
            return ResponseEntity
                    .badRequest()
                    .body("User not found with id: " + id);
        }

        Users existingUser = optionalUser.get();

        existingUser.setEmail(updatedUser.getEmail());
        existingUser.setPassword(updatedUser.getPassword());
        existingUser.setFullName(updatedUser.getFullName());
        existingUser.setRole(updatedUser.getRole());

        Users savedUser = userRepository.save(existingUser);

        return ResponseEntity.ok(savedUser);
    }

    // Delete User
    @DeleteMapping("/{id}")
    public ResponseEntity<?> deleteUser(@PathVariable Long id) {

        Optional<Users> user = userRepository.findById(id);

        if (user.isEmpty()) {
            return ResponseEntity
                    .badRequest()
                    .body("User not found with id: " + id);
        }

        userRepository.deleteById(id);

        return ResponseEntity.ok("User deleted successfully");
    }
}

/**
INSERT INTO `users` (`id`, `email`, `password`, `full_name`, `role`, `created_at`) VALUES ('1', 'fahim@gmail.com', '1122', 'Fahim', 'admin', current_timestamp()), ('2', 's@s.com', '1122', 'Sabbir', 'admin', current_timestamp());
* */