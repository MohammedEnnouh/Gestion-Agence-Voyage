# Android Virtual Device (AVD) Optimization Guide

## Current Issue
Your Android emulator is crashing due to insufficient memory. The JVM is running out of memory when trying to start the AVD.

## Solutions Applied

### 1. Gradle Memory Settings (✅ COMPLETED)
- Increased JVM heap from 2GB to 4GB
- Added metaspace optimization
- Added heap dump on OOM for debugging

### 2. Android Studio VM Options
A custom `studio.vmoptions` file has been created with optimized settings:
- Heap size: 2GB-6GB
- Optimized garbage collection
- Performance improvements

**To apply these settings:**
1. Copy the `studio.vmoptions` file to your Android Studio installation directory
2. Or go to Help → Edit Custom VM Options in Android Studio
3. Paste the contents from `studio.vmoptions`

### 3. AVD Memory Optimization

**Recommended AVD Settings:**
1. **RAM**: Reduce to 2048MB (2GB) or less
2. **VM Heap**: Set to 256MB
3. **Internal Storage**: 2GB minimum
4. **SD Card**: Use file-backed instead of studio-managed
5. **Graphics**: Use "Hardware - GLES 2.0" instead of "Automatic"

**To modify your existing AVD:**
1. Open AVD Manager in Android Studio
2. Click the pencil icon (Edit) next to your "Medium_Phone" AVD
3. Click "Advanced Settings"
4. Adjust the following:
   - RAM: 2048 MB
   - VM Heap: 256 MB
   - Graphics: Hardware - GLES 2.0
   - Enable "Use Host GPU"

### 4. System Memory Management

**Before starting the emulator:**
1. Close unnecessary applications (browsers, IDEs, etc.)
2. Check Task Manager for memory usage
3. Ensure at least 4GB free RAM before starting emulator

**Windows Virtual Memory:**
1. Go to System Properties → Advanced → Performance Settings
2. Click "Advanced" → "Change" (Virtual Memory)
3. Set custom size: Initial 8192MB, Maximum 16384MB

### 5. Alternative Solutions

**If the problem persists:**

**Option A: Use a lighter AVD**
- Create a new AVD with API 28-30 (lighter than newer APIs)
- Use a smaller screen resolution (480x800 instead of 1080x1920)
- Disable animations and unnecessary features

**Option B: Use Physical Device**
- Enable Developer Options on your Android phone
- Enable USB Debugging
- Connect via USB and test directly on device

**Option C: Use Android Emulator from Command Line**
```bash
# Navigate to Android SDK emulator directory
cd %LOCALAPPDATA%\Android\Sdk\emulator

# Start emulator with reduced memory
emulator -avd Medium_Phone -memory 2048 -partition-size 2048
```

## Troubleshooting Commands

**Check Java processes:**
```bash
jps -v
```

**Kill Gradle daemon:**
```bash
./gradlew --stop
```

**Clear Android Studio caches:**
- File → Invalidate Caches and Restart

## Expected Results
After applying these optimizations:
- Emulator should start without crashing
- Build times should improve
- Overall Android Studio performance should be better
- Memory usage should be more stable

## Next Steps
1. Restart Android Studio completely
2. Try starting the emulator again
3. If issues persist, try creating a new AVD with the recommended settings above
