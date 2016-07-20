BUILDDIR := ./build/
BINARY = $(BUILDDIR)TaskWatcher

all :dir TaskWatcher package

ROOTDIR = $(CURDIR)/../

toolchain := $(ROOTDIR)toolchain/
xqlibs := $(ROOTDIR)/
CXX = $(toolchain)/bin/mipsel-openwrt-linux-uclibc-g++
CC =$(toolchain)/bin/mipsel-openwrt-linux-uclibc-gcc


LIB_DIR = -L$(xqlibs)/lib/ -Wl,-rpath-link,$(toolchain)/lib/ 
CXXFLAGS += -I$(xqlibs)/include/ 
LDFLAGS = -Wall -O2 -lxmrouter \
 -lthrift -lssl -lcrypto -lconfig++ -ljson-c -lboost_atomic \
 -lboost_system -lboost_filesystem -lthriftnb -levent -lcurl -lz -lboost_thread -lpthread \
 -lroutermain -std=c++0x

dir : 
	mkdir -p $(BUILDDIR)

TaskWatcher :
	$(CXX) $(CXXFLAGS) JSON.cpp SystemState.cpp TaskWatcher.cpp $(LIB_DIR) $(LDFLAGS) -g -gdwarf-2 -o $(BINARY) 

clean:
	rm -r build

.PHONY : clean TaskWatcher
package:
	cp start_script build/
	../plugin_packager_x64
