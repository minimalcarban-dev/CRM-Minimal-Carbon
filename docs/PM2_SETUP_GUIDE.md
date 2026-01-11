# PM2 Setup Guide (Queue Worker Ko Automatically Chalane Ke Liye)

Is document ka maksad hai `pm2` ko setup karne ka process explain karna, taaki hamare Laravel project ke background jobs (jaise Diamond Import/Export) bina ruke production server par chalti rahein.

## Kaunsi Files Banayi Jayengi Ya Badli Jayengi?

### 1. Nayi Files Jo Banengi (New Files to be Created)

-   **`ecosystem.config.js`**:

    -   Yeh `pm2` ki main configuration file hogi.
    -   Is file mein hum `pm2` ko batayenge ki hamara queue worker process kaise start karna hai. Isme worker ka naam, project ka path, aur `php artisan queue:work` command ki details hongi.

-   **`PM2_SETUP_GUIDE.md`**:
    -   Yeh wohi file hai jise aap abhi padh rahe hain. Yeh aapke reference ke liye banayi gayi hai.

### 2. Files Jinme Badlav Hoga (Files to be Modified)

-   **`package.json`**:
    -   Is file mein hum `pm2` ko as a `devDependency` add karenge.
    -   Aisa karne se, `npm install` command chalane par `pm2` hamare project mein install ho jayega.

## Implementation Ke Steps

1.  **PM2 Install Karna**: Hum `npm install pm2 --save-dev` command se `pm2` ko project mein install karenge.
2.  **Configuration File Banana**: `ecosystem.config.js` file banayenge aur usme queue worker ki settings daalenge.
3.  **Worker Start Karna**: `pm2 start ecosystem.config.js` command se hum queue worker ko start karenge.
4.  **Process List Save Karna**: `pm2 save` command se hum current process ko save karenge taaki server restart hone par bhi `pm2` ko yaad rahe ki kaunsa process chalana hai.
5.  **Startup Script Banana**: `pm2 startup` command chalayenge. Yeh command ek script generate karegi jise server ke startup mein add karna hota hai. Isse server jab bhi on hoga, `pm2` aur hamara queue worker automatically start ho jayega.

## Kya Critical Issues Aa Sakte Hain? (Potential Critical Issues)

Implementation ke time hum in issues ka saamna kar sakte hain:

1.  **PHP Path Ka Na Milna**:

    -   **Issue**: `pm2` `php artisan queue:work` command chalata hai. Agar server ke system environment (PATH) mein `php` ka path set nahi hai, toh `pm2` `php` command ko dhoond nahi payega aur worker fail ho jayega.
    -   **Solution**: Humein server par PHP ka path globally set karna hoga.

2.  **Permission Ki Samasya (Permissions Issue)**:

    -   **Issue**: `pm2 startup` command system-level changes karti hai taaki boot hone par `pm2` chal sake. Iske liye administrator (ya root) permissions ki zaroorat pad sakti hai.
    -   **Solution**: Humein yeh command administrator/root access ke saath chalani hogi.

3.  **Node.js ya NPM Ka Sahi Se Install Na Hona**:

    -   **Issue**: Agar server par Node.js ya NPM theek se install nahi hai ya unka path galat hai, to `pm2` install hi nahi ho payega.
    -   **Solution**: Humein pehle Node.js aur NPM ko aache se install karke unka path set karna hoga.

4.  **Galat Project Path**:
    -   **Issue**: Agar `ecosystem.config.js` file mein project ka directory path galat daal diya, to worker start nahi hoga kyunki use `artisan` file nahi milegi.
    -   **Solution**: Humein configuration file mein project ka absolute path bilkul sahi dena hoga.

Yeh saari jaankari aapke reference ke liye hai. Please isse padh lein aur batayein agar aapke koi questions hain. Aapki permission ke baad hum aage badhenge.
