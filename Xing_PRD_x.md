# Product Overview

Target Market: This product targets online vendors and service providers who mainly use social media to market their products and close sales with their customers. 

## User Personas

1. Social Media Savvy Entrepreneur

| Name: Lisa | Location: Urban Area |
| :---- | :---- |
| Age: 24 | Income: Moderate |
| Occupation: Fashion Designer who runs an Online Fashion Business. | Background: Degree in Fashion Design. |

She started her boutique 3 years ago and uses WhatsApp,  Instagram, and Facebook as her primary sales channels.

Her Goals: Increase brand visibility, streamline the process of responding to customer inquiries, and boost online sales.

Frustrations: She struggles to manage multiple customer conversations across different platforms. She can’t create enough time to respond to her customers on time because tailoring keeps her very busy (she struggles with balancing her production with addressing customers’ inquiries), and finds it challenging to keep track of her sales and revenue.

Technology Usage: Heavy user of WhatsApp,  Instagram, Facebook, and Pinterest.

Uses a smartphone for business operations.

Needs:

* An assistant that helps her interact with her customers while she focuses on tailoring to meet their orders.  
* A tool to integrate all customer communications into a single platform.  
* An easy-to-use system for managing orders, sales, and revenue.  
    
2. Freelance Digital Marketer

| Name: Tom | Location: Suburban Area |
| :---- | :---- |
| Age: 34 | Income: Above Average |
| Occupation: Freelance Software Engineer | Background: Degree in Computer Science and over 8 years of experience in software development. |

Tom builds websites and mobile apps for his clients and uses social media to showcase his work. He often gets messages from potential clients on these platforms.

His Goals: Boost sales.

Frustrations: He is always deeply engrossed in coding when responding to potential clients on time. Sometimes he responds to potential clients up to 3 days after their inquiry and often gets disappointed to find out that they have engaged the services of someone else.

Technology Usage: He uses a range of programming software. He primarily works on a high-performance laptop. He uses his phone and laptop to post and interact with his clients on LinkedIn, X, WhatsApp, IG, Facebook.

Needs:

* An assistant interacts with his customers while he focuses on software development and alerts him when software is requested.

3. Part-time Craft Seller  
   

| Name: Bisi | Location: Suburban Area |
| :---- | :---- |
| Age: 38 | Income: Low to Moderate |
| Occupation: Food vendor | Background: Primary education, stay-at-home mom. |

Uses social media platforms to sell freshly cooked meals.

Goals: Increase sales, and connect with a larger audience.

Frustrations: Limited technical skills. She has to cater to her children, cook, and deliver the meals, and because of this, she finds it difficult to chat with enough clients and take all the orders that come her way daily. 

Technology Usage: She uses WhatsApp and Facebook on her smartphone, takes pictures of the meals she cooks, and posts on these platforms.

Needs:

* A simple platform that can assist her with taking orders while she focuses on cooking.  
* Easy-to-understand tutorials and support.

## Use Cases

1. Use Case: Unified Messaging  
* Actor: Social Media Savvy Entrepreneur (Lisa)  
* Description: Lisa wants to manage all her customer inquiries from Instagram, Facebook, and WhatsApp in one place.  
* Steps:  
- Lisa logs into the platform.  
- Connects her Instagram, Facebook, and WhatsApp accounts.  
- Receives and responds to customer messages from all platforms in a single unified inbox.  
* Outcome: Lisa can efficiently manage all customer communications without switching between different apps.

2. Use Case: AI Assistant Chatbot  
* Actor: Freelance Software Developer (Tom)  
* Description: Tom wants his client’s messages responded to on time and all the orders communicated to him daily.  
* Steps:  
- Lisa logs into the platform.  
- Connects her Instagram, Facebook, and WhatsApp accounts.  
- Uploads information about his services to the knowledge base to help the chatbot understand what services he offers and how much he charges.  
- The Chatbot receives and responds to customer messages on the different platforms and compiles a list of orders for Tom to address at the end of the day.  
* Outcome: Tom can increase his response time to customer inquiries while having more time to focus on software development.

3. Use Case: Business Intelligence Analytics  
* Actor: Online Food Vendor (Bisi)  
* Description: Bisi wants to respond to all customer inquiries daily and keep track of her business growth.  
* Steps:  
- Lisa logs into the platform.  
- Connects her Instagram, Facebook, and WhatsApp accounts.  
- Uploads information about her services to the knowledge base daily.  
- The Chatbot receives and responds to customer messages on the different platforms and compiles a list of orders for her to address at the end of each day.  
- She uses the analytics dashboard to keep track of her sales and revenue.  
* Outcome: Bisi doesn’t miss any orders and can see what types of food she should cook more based on her knowledge of the sales frequency.

# Functional Requirements

Core Features:

* Landing page  
* User Authentication  
* Chat Interface \- Omni Channel  
* Chat automation on social media platforms- starting with WhatsApp and scaling subsequently to websites (widgets), Instagram, Facebook,  LinkedIn, telegram, email, SMS, WeChat, line chat, x, TikTok, etc.  
* Product Database  
* Data input system via Interactive Chatbot (inventory entry)  
* Knowledge base interface (library similar to Google Drive)  
* Deep Learning Vector database for Retrieval Augmented Generation   
* LLM behind the scenes.  
* Payment feature  
* Analytics Dashboard

# Non-Functional Requirements

#### **Performance**

* **Responsiveness:** The system should respond to user actions within 2 seconds for 95% of interactions.  
* **Throughput:** The platform should be capable of handling up to 10,000 concurrent users without significant performance degradation.  
* **Availability:** The system should have an uptime of 99.9% to ensure it is accessible to users at all times.  
* **Load Time:** The average page load time should be under 3 seconds, even under peak load conditions.

#### **Scalability**

* **Horizontal Scalability:** The platform should support horizontal scaling to handle increasing loads by adding more servers.  
* **Vertical Scalability:** The platform should be able to upgrade individual servers to handle higher performance requirements.  
* **Elasticity:** The system should automatically scale resources up or down based on current demand, ensuring efficient resource utilization.  
* **Database Scalability:** The database should be able to handle a growing amount of data and support distributed database management if needed.

#### **Security**

* **Data Encryption:** All user data should be encrypted both at rest and in transit using industry-standard encryption methods (e.g., AES-256).  
* **Authentication and Authorization:** The platform should implement robust authentication mechanisms (e.g., OAuth, two-factor authentication) and ensure users have access only to the functionalities they are authorized to use.  
* **Data Privacy:** The system must comply with data privacy regulations such as GDPR and CCPA, ensuring users' data is handled with the highest standards of privacy.  
* **Regular Security Audits:** Regular security audits should be conducted to identify and mitigate potential vulnerabilities.

#### **Usability**

* **Intuitive Interface:** The user interface should be intuitive and easy to navigate, requiring minimal training for new users.  
* **Accessibility:** The platform should be accessible to users with disabilities, complying with WCAG 2.1 standards.  
* **User Feedback:** There should be mechanisms for users to provide feedback on the usability of the platform, which will be used to make continuous improvements.  
* **Consistent User Experience:** The design and functionality should be consistent across all devices and platforms (e.g., desktop, mobile, tablet).

# UI/UX Design 

**Features**:

1. Landing page  
   **Home Page:**  
   * Overview of the product, key features, and primary call to action (e.g., "Sign Up Now" or "Join the Waitlist").  
   * Detailed descriptions of all features, including Unified Messaging, AI Assistant Chatbot, Business Intelligence Analytics, Integrated Payments, and more.  
   * How it works: Step-by-step guide on setting up and using the platform, with visuals or videos for better understanding.

   **Pricing:**

   * Information on subscription plans, pricing tiers, and any free trial options.

   **Company:**

   * Background information about the company, its mission, vision, and team members.

   **Use Cases:**

   * User testimonials and success stories from current customers, showcasing the platform's benefits.  
   * Different use cases identified with videos.  
   * Frequently asked questions and their answers to help users quickly find information.

   **Blog:**

   * Articles and updates on industry trends, tips for social media marketing, and how to use the platform effectively.

   **Contact Us:**

   * Qinsight’s contact information, including email, phone number, and a contact form for inquiries.

   **Support:**

   * Resources for users, including tutorials, guides, and a knowledge base.  
   * After login, users can access their account settings, connected social media accounts, and analytics dashboard.

   **Legal \- Terms and Privacy Policy:**

   * Detailed information on how user data is collected, used, and protected.  
   * Legal terms and conditions for using the platform.

   

2. User Authentication ✅Done  
   1. Users should be able to sign up with Facebook, Google, or manually.  
   2. After users sign up, they should be prompted to register their WhatsApp \- ask for their WhatsApp number.  
   3. Verification via email and WhatsApp  
        
3. Chat Interface \- Omni Channel  
   1. Button to toggle on-off AI chat per chat  
   2. Can share and receive texts, pictures, video, and audio  
   3. Payment interface  
   4. Search and Filter chat (AI/Person, Prospect, Client, Customer,  )  
        
4. Inventory  
   1. Data input system via Interactive Chatbot (inventory entry)  
      1. Can share and receive texts, pictures, video, audio, spreadsheet, pdf. (Users should be asked if the data entered should be saved to their knowledge base or a one-time (should be on by default))  
      2. Logistics/Delivery Info

   2. Knowledge base interface (library similar to Google Drive)  
      1. Stores up the data collected through the data input system. Expected Product data:  
         1. Title: the name of the product  
            2. Images: the main image and additional angles of the product  
            3. Description: what the product is  
            4. Price: how much the product costs  
            5. Categorization: the department or category the product falls under  
            6. Availability: whether the product is in stock  
            7. Quantity: how much of the product there is  
            8. Bullet points: important features or selling points  
            9. Other key attributes: material, size, color, age, gender, special features, etc.  
      2. Contacts  
      3. Limited storage (users can subscribe to increase their storage)  
           
5. PRODUCT DATABASE \- [Kiqi Database schema](https://docs.google.com/spreadsheets/d/14FJmphBYIRefWFW-xwzouc-rcCkPP8So5CC0xe0M14c/edit?usp=sharing)  
   The database schema for this Product Information Management (PIM) software is designed to integrate seamlessly with the Amazon Product API and various e-commerce platforms like Shopify, WooCommerce, Jumia, Konga, and Amazon. It serves as a centralized hub for managing product listings, inventory, and logistics across multiple sales channels, including social media. The schema includes tables for products, categories, inventory, and logistics. Each product record is structured to accommodate the necessary attributes and specifications required by the Amazon API, ensuring compatibility and synchronization. The inventory tables are linked to product entries, allowing for real-time tracking of stock levels and facilitating efficient inventory management. Additionally, logistics tables are integrated to manage delivery information, supporting various shipping methods and tracking deliveries in real time. This comprehensive schema enables businesses to streamline operations, improve product visibility, and enhance customer satisfaction by ensuring consistent product information and availability across all sales channels

in the given schema, the hierarchy of `User`, `Store`, `Product`, and `Product Variant` delineates the structured relationship and organizational logic of an e-commerce platform designed to manage extensive product listings across multiple storefronts.

### **Hierarchy Description:**

1. **User**:  
   * **Definition**: A `User` represents an individual or entity that owns or has administrative rights over one or more `Stores` on the platform. Users are the primary actors who manage product listings, store settings, and user permissions.  
   * **Multiple Stores**: A single `User` can own or operate multiple `Stores`, each catering to different market segments or product lines.  
   * **Access Control**: Users have the capability to grant access to other users, allowing them to manage or modify certain aspects of the store, thereby implementing a controlled access environment.  
2. **Store**:  
   * **Definition**: A `Store` functions as a virtual storefront representing a collection of `Products`. Each store operates under the management of at least one `User` but can include multiple users with various levels of access permissions.  
   * **Access Control**: The primary user of a store can delegate responsibilities and permissions to other users, allowing for collaborative management of the store’s inventory and sales operations.  
   * **Product Listings**: No product can be sold without being listed in a store, which ensures that all sales activities are organized and tracked under specific storefronts.  
3. **Product**:  
   * **Definition**: Within each `Store`, there are various `Products`, each representing a specific item for sale. Products define the basic units of sale and are categorized or detailed according to type, use, or preference.  
   * **Multiplicity**: A single `Product` can appear in more than one `Store`. This flexibility allows for products to be marketed through multiple channels or storefronts, increasing their visibility and potential market reach. As we log more products it increases our ability to allow other users sell the same thing.  
   * **Variants**: Each product can have multiple `Product Variants`, which are specific versions or modifications of the product, such as different sizes, colors, or configurations.  
4. **Product Variant**:  
   * **Definition**: A `Product Variant` is a specific iteration of a `Product` that offers different attributes or configurations, allowing customers to choose according to their preferences.  
   * **Fixed Association**: While a product can exist in multiple stores, each variant of a product is permanently tied to its parent product, ensuring consistency in product specifications and variant offerings across different stores.  
   * **Unique Identification**: Each variant is uniquely identified within the system, enabling precise tracking and management of stock levels, pricing, and sales data specific to each variant.

   ### **Operational Flow:**

In practice, a `User` logs into the platform, accesses a `Store` they manage, and reviews or updates the list of `Products`. For each product, specific `Product Variants` are managed to cater to diverse customer needs. The user can adjust pricing, manage inventory, or run promotions at both the product and variant levels. Access control within the store allows the primary user to maintain overall management while delegating certain responsibilities to trusted users, facilitating a collaborative yet controlled management environment.

This hierarchical structure not only simplifies the management of extensive product lists across multiple storefronts but also enhances the platform's flexibility and scalability, catering to a diverse range of user needs and business models.

6. Analytics Dashboard  
   1. Shows the total number of orders daily, monthly, and yearly \- Number card  
      Example: ![][image1]  
   2. Shows the total revenue daily, monthly, and yearly \- Number card  
   3. Line graph showing daily (and monthly) sales trend  
   4. Show the top 5 selling products  
   5. Show the top 5 customers  
        
7. User Profile and settings  
1. Business Profile set up  
   1. Profile picture  
   2. Business name  
   3. About  
   4. Return Policy  
   5. Address \- Country, state, business address  
   6. Email   
   7. Alternative Phone number   
   8. Password update  
2. Add Accounts  
   1. Facebook coming soon  
   2. Instagram coming soon  
   3. Website widget coming soon  
3. Payment Set Up  
   1. Currency  
   2. Add payment method \- account number  
4. Sales Report Setting  
   1. Set frequency sales report notification \- daily or weekly  
   2. Set preferred time sales report notification  
   3. Template for sales report notification \- should contain a list of products ordered with customers’ names, phone numbers, and addresses; total sales amount for the period.  
5. Subscription page  
   1. Show [subscription plans](https://docs.google.com/document/d/1p6ZvDRGMABevmvWfq2P26PIelx2r_tpRdgPWBzBP79k/edit?usp=sharing)

7\.  Security

1. Password management

# Technical Specifications

System Architecture Overview:

## Technology Stack

**Frontend**:

* HTML: Used for structuring the web pages and defining the content.  
* Tailwind CSS: Provides utility classes for styling the user interface components efficiently.  
* ReactJS: A JavaScript library for building interactive user interfaces, utilized for creating dynamic and responsive frontend components.

**Backend**:

* Javascript: Server-side scripting language used for processing user requests, interacting with the database, and implementing business logic.  
* MongoDB: Relational database management system responsible for storing and managing innovation data.  
* Hosted on Google Cloud Platform: Provides a scalable and reliable infrastructure for hosting the web application and database, ensuring high availability and performance.

## APIs and Integration Points

* WhatsApp API

## Architecture Breakdown

8. Customer Support Chat Interface \- Generative AI Chatbot

   

9. Inventory  
   1. Data input system via Interactive Chatbot (inventory entry) \- Rules Based Chatbot

   2. Knowledge base interface (library similar to Google Drive) \- 

10. Analytics Dashboard \- 

    

11. User Profile and settings \- 

# Xinng Project Timeline

| Phase | Breakdown | Timeline |
| ----- | ----- | ----- |
| **Software Design** | **Designing the architecture and user interface of the software.** | **Week 1 \- 3 Sept 1 \- 21** |
| **Software Development** | **Frontend** | **Week 4 \- 9 Sep 22 \- Nov 3** |
|  | **Backend** |  **Week 1 \- 12 Sep 1 \- Nov 24** |
|  | **AI** |  **Week 3 \- 9 Sep 14 \- Nov 3** |
| **Waitlist** | **Start Marketing by creating a waitlist**  | **Week 10 Nov 3** |
| **Product Launch** | **Marketing by running an online Ad campaign** | **Week 13 Dec 1📌** |

[image1]: <data:image/png;base64,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>